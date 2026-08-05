<?php

namespace App\Http\Controllers\Be;

use App\Http\Controllers\Controller;
use App\Http\Requests\Be\StoreUpdateBookReviewRequest;
use App\Http\Resources\BookReviewResource;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Support\AuditLogger;

class BookReviewsController extends Controller
{
    public function list(Request $request): Response
    {
        $this->authorize('viewAny', BookReview::class);

        return Inertia::render('be/BookReviews/List', [
            'pageTitle' => 'Book Reviews',
            'pageDescription' => 'Create, track, approve, or reject BookHive reviews based on your role.',
            'canCreate' => $request->user()->isSuperAdmin() || $request->user()->can('reviews.create') || $request->user()->can('reviews.manage'),
            'fetchUrl' => route('fetch.bookReviews'),
            'listMode' => 'all',
        ]);
    }

    public function myReviews(Request $request): Response
    {
        $this->authorize('viewAny', BookReview::class);

        return Inertia::render('be/BookReviews/List', [
            'pageTitle' => 'My Reviews',
            'pageDescription' => 'Track your pending, approved, and rejected book reviews.',
            'canCreate' => $request->user()->isSuperAdmin() || $request->user()->can('reviews.create') || $request->user()->can('reviews.manage'),
            'fetchUrl' => route('fetch.bookReviews') . '?scope=mine',
            'listMode' => 'mine',
        ]);
    }

    public function moderationQueue(Request $request): Response
    {
        if (! ($request->user()->isSuperAdmin() || $request->user()->can('reviews.approve'))) {
            abort(403, 'You do not have permission to moderate reviews.');
        }

        return Inertia::render('be/BookReviews/List', [
            'pageTitle' => 'Review Moderation',
            'pageDescription' => 'Review pending feedback, approve high-quality reviews, or reject reviews with a moderation note.',
            'canCreate' => false,
            'fetchUrl' => route('fetch.bookReviews') . '?status=pending',
            'listMode' => 'moderation',
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', BookReview::class);

        return $this->renderForm($request, null, null);
    }

    public function createForBook(int $book, Request $request): Response
    {
        $this->authorize('create', BookReview::class);

        $selectedBook = Book::published()->findOrFail($book);

        return $this->renderForm($request, null, $selectedBook);
    }

    public function edit(int $id, Request $request): Response
    {
        $review = BookReview::with(['book', 'createdBy', 'approvedBy'])->findOrFail($id);

        $this->authorize('update', $review);

        if (! $review->canBeModifiedBy($request->user()) && ! $review->canBeModeratedBy($request->user())) {
            abort(403, 'This review cannot be edited by your account.');
        }

        return $this->renderForm($request, $review, null);
    }

    public function moderationPage(int $id, Request $request): Response
    {
        $review = BookReview::with(['book', 'createdBy', 'approvedBy'])->findOrFail($id);

        $this->authorize('moderate', $review);

        if (! $review->canBeModeratedBy($request->user())) {
            abort(403, 'You cannot moderate this review.');
        }

        return Inertia::render('be/BookReviews/Moderation', [
            'pageTitle' => 'Moderate Review',
            'pageDescription' => 'Approve or reject a pending review and leave a clear moderation note if needed.',
            'review' => BookReviewResource::make($review)->resolve($request),
            'statuses' => BookReview::STATUSES,
            'formUrl' => route('dashboard.be.bookReviews.moderate', $review->id),
        ]);
    }

    public function storeUpdate(StoreUpdateBookReviewRequest $request, int $id = 0): RedirectResponse
    {
        $actor = $request->user();
        $review = $id ? BookReview::findOrFail($id) : new BookReview();
        $validated = $request->validated();
        $oldValues = $review->exists ? $review->only(['book_id', 'created_by', 'rating', 'title', 'body', 'status', 'moderation_note', 'approved_by', 'approved_at']) : [];
        $canModerate = $actor->isSuperAdmin() || $actor->can('reviews.approve');

        if ($review->exists) {
            $this->authorize('update', $review);

            if (! $review->canBeModifiedBy($actor) && ! $review->canBeModeratedBy($actor)) {
                abort(403, 'This review cannot be edited by your account.');
            }
        } else {
            $this->authorize('create', BookReview::class);
        }

        if (! $review->exists && ! ($actor->isSuperAdmin() || $actor->can('reviews.create') || $actor->can('reviews.manage'))) {
            abort(403, 'You cannot create reviews.');
        }

        if ($review->is_protected && ! $actor->isSuperAdmin()) {
            abort(403, 'Protected reviews cannot be modified by public/demo users.');
        }

        $payload = [];

        if (! $review->exists || $review->canBeModifiedBy($actor)) {
            $payload = array_filter([
                'book_id' => $validated['book_id'] ?? $review->book_id,
                'rating' => $validated['rating'] ?? $review->rating,
                'title' => $validated['title'] ?? $review->title,
                'body' => $validated['body'] ?? ($validated['content'] ?? $review->body),
                'content' => $validated['content'] ?? ($validated['body'] ?? $review->content),
            ], fn ($value) => $value !== null);
        }

        if (! $review->exists) {
            $payload['created_by'] = $actor->id;
            $payload['status'] = BookReview::STATUS_PENDING;
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
            $payload['is_seeded'] = false;
            $payload['is_protected'] = false;
        } elseif ($canModerate && array_key_exists('status', $validated)) {
            $payload['status'] = $validated['status'];
        }

        if ($canModerate) {
            $payload['moderation_note'] = $validated['moderation_note'] ?? $review->moderation_note;

            if (($payload['status'] ?? $review->status) === BookReview::STATUS_APPROVED) {
                $payload['approved_by'] = $actor->id;
                $payload['approved_at'] = now();
            } elseif (array_key_exists('status', $payload)) {
                $payload['approved_by'] = null;
                $payload['approved_at'] = null;
            }
        }

        $review->fill($payload)->save();

        AuditLogger::record($id ? 'edit review' : 'create review', $review, $id ? 'Book review updated.' : 'Book review submitted and queued for moderation.', $oldValues, $review->only(['book_id', 'created_by', 'rating', 'title', 'body', 'status', 'moderation_note', 'approved_by', 'approved_at']), $actor, $request);

        return redirect()
            ->route('dashboard.be.books.show', $review->book_id)
            ->with('success', $id ? 'Review updated successfully.' : 'Review submitted successfully and is pending moderation.');
    }

    public function moderate(int $id, Request $request): RedirectResponse
    {
        $review = BookReview::findOrFail($id);

        $this->authorize('moderate', $review);

        if (! $review->canBeModeratedBy($request->user())) {
            abort(403, 'You cannot moderate this review.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,pending'],
            'moderation_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [
            'status' => $validated['status'],
            'moderation_note' => $validated['moderation_note'] ?? $review->moderation_note,
        ];

        if ($validated['status'] === BookReview::STATUS_APPROVED) {
            $payload['approved_by'] = $request->user()->id;
            $payload['approved_at'] = now();
        } else {
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
        }

        $oldValues = $review->only(['status', 'moderation_note', 'approved_by', 'approved_at']);
        $review->update($payload);

        AuditLogger::record($validated['status'] === BookReview::STATUS_APPROVED ? 'approve review' : ($validated['status'] === BookReview::STATUS_REJECTED ? 'reject review' : 'edit review'), $review, 'Review moderation status changed.', $oldValues, $review->only(['status', 'moderation_note', 'approved_by', 'approved_at']), $request->user(), $request);

        return back()->with('success', 'Review status updated successfully.');
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $review = BookReview::findOrFail($id);

        $this->authorize('delete', $review);

        if (! $review->canBeDeletedBy($request->user())) {
            abort(403, 'This protected review cannot be deleted.');
        }

        $bookId = $review->book_id;
        $oldValues = $review->only(['book_id', 'created_by', 'rating', 'title', 'status']);
        $review->delete();

        AuditLogger::record('delete review', $review, 'Book review soft-deleted.', $oldValues, [], $request->user(), $request);

        return redirect()->route('dashboard.be.books.show', $bookId)->with('success', 'Review deleted successfully.');
    }

    private function renderForm(Request $request, ?BookReview $review = null, ?Book $selectedBook = null): Response
    {
        $canModerate = $request->user()->isSuperAdmin() || $request->user()->can('reviews.approve');

        return Inertia::render('be/BookReviews/Form', [
            'pageTitle' => $review ? 'Edit Book Review' : 'Create Book Review',
            'pageDescription' => $review
                ? 'Review owners can update pending/rejected reviews. Moderators can approve or reject reviews.'
                : 'New reviews are saved as pending until a moderator approves them.',
            'pageData' => $review ? BookReviewResource::make($review)->resolve($request) : null,
            'selectedBook' => $selectedBook,
            'booksList' => Book::published()->orderBy('title')->get(['id', 'title', 'author']),
            'statuses' => BookReview::STATUSES,
            'canModerate' => $canModerate,
            'formUrl' => $review ? route('dashboard.be.bookReviews.storeUpdate', $review->id) : route('dashboard.be.bookReviews.storeUpdate'),
        ]);
    }
}

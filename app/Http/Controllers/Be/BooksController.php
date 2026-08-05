<?php

namespace App\Http\Controllers\Be;

use App\Http\Controllers\Controller;
use App\Http\Requests\Be\StoreUpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookReviewResource;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\AuditLogger;
use Inertia\Inertia;
use Inertia\Response;

class BooksController extends Controller
{
    public function list(Request $request): Response
    {
        $this->authorize('viewAny', Book::class);

        return Inertia::render('be/Books/List', [
            'pageTitle' => 'Books',
            'pageDescription' => 'Browse, search, and manage the BookHive catalog. Ratings are calculated from approved reviews only.',
            'canCreate' => $request->user()->isSuperAdmin() || $request->user()->can('books.manage'),
        ]);
    }

    public function show(int $id, Request $request): Response
    {
        $book = Book::with(['createdBy:id,name', 'approvedReviews.createdBy:id,name'])
            ->findOrFail($id);

        $this->authorize('view', $book);

        if (! $book->canBeViewedBy($request->user())) {
            abort(403, 'This draft book is only visible to book managers.');
        }

        $visibleReviews = $book->visibleReviewsFor($request->user())
            ->with(['createdBy:id,name', 'approvedBy:id,name'])
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('be/Books/Detail', [
            'pageTitle' => $book->title,
            'pageDescription' => 'Book detail, approval-safe review summary, and approved reader feedback.',
            'book' => BookResource::make($book)->resolve($request),
            'reviews' => BookReviewResource::collection($visibleReviews),
            'canManage' => $book->canBeModifiedBy($request->user()),
            'canCreateReview' => $request->user()->isSuperAdmin() || $request->user()->can('reviews.create') || $request->user()->can('reviews.manage'),
            'createReviewUrl' => route('dashboard.be.books.reviews.create', $book->id),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Book::class);

        return Inertia::render('be/Books/Form', [
            'pageTitle' => 'Create Book',
            'pageDescription' => 'Add a new draft or published book to the BookHive catalog.',
            'pageData' => null,
            'statuses' => Book::STATUSES,
            'formUrl' => route('dashboard.be.books.storeUpdate'),
        ]);
    }

    public function edit(int $id, Request $request): Response
    {
        $book = Book::findOrFail($id);

        $this->authorize('update', $book);

        if (! $book->canBeModifiedBy($request->user())) {
            abort(403, 'This protected book cannot be edited by your account.');
        }

        return Inertia::render('be/Books/Form', [
            'pageTitle' => 'Edit Book',
            'pageDescription' => 'Update book metadata. Slugs are regenerated safely from the title.',
            'pageData' => BookResource::make($book)->resolve($request),
            'statuses' => Book::STATUSES,
            'formUrl' => route('dashboard.be.books.storeUpdate', $id),
        ]);
    }

    public function storeUpdate(StoreUpdateBookRequest $request, int $id = 0): RedirectResponse
    {
        $actor = $request->user();
        $book = $id ? Book::findOrFail($id) : new Book();

        $this->authorize($book->exists ? 'update' : 'create', $book->exists ? $book : Book::class);

        if ($book->exists && ! $book->canBeModifiedBy($actor)) {
            abort(403, 'This protected book cannot be edited by your account.');
        }

        $validated = $request->validated();
        $oldValues = $book->exists ? $book->only(['title', 'slug', 'ISBN_10', 'ISBN_13', 'author', 'category', 'cover_image', 'description', 'published_year', 'status']) : [];
        $coverImage = $validated['cover_image'] ?? $book->cover_image;

        if ($request->hasFile('cover_image_file')) {
            if ($book->cover_image && str_starts_with($book->cover_image, 'book-covers/')) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $coverImage = $request->file('cover_image_file')->store('book-covers', 'public');
        }

        $book->fill([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title'], $book->id),
            'ISBN_10' => $validated['ISBN_10'] ?? null,
            'ISBN_13' => $validated['ISBN_13'] ?? null,
            'author' => $validated['author'],
            'category' => $validated['category'] ?? null,
            'cover_image' => $coverImage,
            'description' => $validated['description'] ?? null,
            'published_year' => $validated['published_year'] ?? null,
            'status' => $validated['status'] ?? Book::STATUS_PUBLISHED,
            'created_by' => $book->created_by ?: $actor->id,
            'is_seeded' => $book->is_seeded ?? false,
            'is_protected' => $book->is_protected ?? false,
        ])->save();

        $book->refreshReviewStats();

        AuditLogger::record($id ? 'edit book' : 'create book', $book, $id ? 'Book updated.' : 'Book created.', $oldValues, $book->only(['title', 'slug', 'ISBN_10', 'ISBN_13', 'author', 'category', 'cover_image', 'description', 'published_year', 'status']), $actor, $request);

        return redirect()->route('dashboard.be.books.show', $book->id)->with('success', $id ? 'Book updated successfully.' : 'Book created successfully.');
    }

    public function remove(int $id, Request $request): RedirectResponse
    {
        $book = Book::findOrFail($id);

        $this->authorize('delete', $book);

        if (! $book->canBeDeletedBy($request->user())) {
            abort(403, 'This protected book cannot be deleted.');
        }

        $oldValues = $book->only(['title', 'slug', 'author', 'category', 'status']);
        $book->delete();

        AuditLogger::record('delete book', $book, 'Book soft-deleted.', $oldValues, [], $request->user(), $request);

        return redirect()->route('dashboard.be.books.list')->with('success', 'Book deleted successfully.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'book';
        $slug = $baseSlug;
        $counter = 2;

        while (Book::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookReviewResource;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClientApiController extends Controller
{
    /**
     * @param mixed $data
     */
    private function success($data = null, string $message = 'Request completed successfully.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param mixed $data
     */
    private function error(string $message = 'Request failed.', int $status = 500, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Backward-compatible wrapper kept for older client integrations.
     *
     * @param mixed $data
     */
    public function sendResponse($data, $message, $status = 200): JsonResponse
    {
        return $this->success($data, $message, $status);
    }

    /**
     * Backward-compatible wrapper kept for older client integrations.
     *
     * @param mixed $errorData
     */
    public function sendError($errorData, $message, $status = 500): JsonResponse
    {
        return $this->error($message, $status, $errorData);
    }

    public function register(Request $request): JsonResponse
    {
        $input = $request->only('name', 'email', 'password', 'c_password', 'password_confirmation', 'role');

        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(User::PUBLIC_REGISTER_ROLES)],
            'password' => ['required', 'string', 'min:8'],
            'c_password' => ['nullable', 'same:password'],
            'password_confirmation' => ['nullable', 'same:password'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = $validator->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_demo' => false,
            'is_protected' => false,
            'status' => 'active',
        ]);
        $user->syncRoles([$data['role']]);
        $user->load('roles', 'permissions');

        $token = JWTAuth::fromUser($user);

        AuditLogger::record('api register', $user, 'Public API account registered.', [], [
            'role' => $data['role'],
            'email' => $user->email,
        ], $user, $request);

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $this->userPayload($user),
        ], 'User registered successfully.', 201);
    }

    public function ensureIsNotRateLimited(Request $request): bool|array
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            event(new Lockout($request));

            return ['seconds' => RateLimiter::availableIn($this->throttleKey($request))];
        }

        return false;
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->get('email')) . '|' . $request->ip());
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $input = $request->only('email', 'password');
        $validator = Validator::make($input, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $rateLimitResponse = $this->ensureIsNotRateLimited($request);
        if ($rateLimitResponse !== false) {
            return $this->error(
                'Too many login attempts. Please try again in ' . $rateLimitResponse['seconds'] . ' seconds.',
                429,
                ['retry_after_seconds' => $rateLimitResponse['seconds']]
            );
        }

        try {
            if (! $token = JWTAuth::attempt($request->only('email', 'password'))) {
                RateLimiter::hit($this->throttleKey($request));

                return $this->error('Invalid login credentials.', 401);
            }

            RateLimiter::clear($this->throttleKey($request));
            $user = JWTAuth::setToken($token)->authenticate();

            if (! $user) {
                JWTAuth::setToken($token)->invalidate(true);

                return $this->error('Authenticated user could not be found.', 401);
            }

            if (($user->status ?? 'active') !== 'active') {
                JWTAuth::setToken($token)->invalidate(true);

                return $this->error('This account is disabled. Please contact the demo owner.', 403);
            }

            $user->forceFill(['last_login_at' => now()])->save();
            $user->load('roles', 'permissions');

            AuditLogger::record('api login', $user, 'User logged in through the JWT API.', [], [], $user, $request);

            return $this->success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => $this->userPayload($user),
                'redirect_url' => '/dashboard',
            ], 'Logged in successfully.');
        } catch (JWTException $exception) {
            return $this->error('JWT authentication failed.', 422, ['error' => $exception->getMessage()]);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            JWTAuth::parseToken()->invalidate(true);

            AuditLogger::record('api logout', $user, 'User logged out through the JWT API.', [], [], $user, $request);

            return $this->success(null, 'Logged out successfully.');
        } catch (JWTException $exception) {
            return $this->error('Unable to invalidate the current token.', 422, ['error' => $exception->getMessage()]);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->error('Unauthenticated.', 401);
        }

        return $this->success([
            'user' => $this->userPayload($user->load('roles', 'permissions')),
        ], 'Current user retrieved successfully.');
    }

    public function getUser(Request $request): JsonResponse
    {
        return $this->me($request);
    }

    public function getJwtToken(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return $this->success(null, 'CSRF/JWT compatibility endpoint is reachable. Use POST /api/client/login to get a JWT token.');
        }

        return new Response('', 204);
    }

    public function books(Request $request): JsonResponse
    {
        $sortFields = ['id', 'title', 'slug', 'ISBN_10', 'ISBN_13', 'author', 'category', 'status', 'average_rating', 'total_reviews', 'created_at', 'updated_at'];
        $sortField = in_array($request->input('sort_field', 'created_at'), $sortFields, true) ? $request->input('sort_field', 'created_at') : 'created_at';
        $sortOrder = $request->input('sort_order') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) $request->input('per_page', 8), 1), 50);
        $search = mb_substr(trim((string) $request->input('search', '')), 0, 100);

        $query = Book::published()
            ->with('createdBy:id,name')
            ->orderBy($sortField, $sortOrder);

        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function (Builder $bookQuery) use ($like) {
                $bookQuery->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('ISBN_10', 'like', $like)
                    ->orWhere('ISBN_13', 'like', $like)
                    ->orWhere('author', 'like', $like)
                    ->orWhere('category', 'like', $like);
            });
        }

        $paginator = $query->paginate($perPage)->withQueryString();
        $payload = BookResource::collection($paginator)->response()->getData(true);

        return $this->success($payload, 'Published books retrieved successfully.');
    }

    public function showBook(string $book, Request $request): JsonResponse
    {
        $model = $this->findPublishedBook($book);

        if (! $model) {
            return $this->error('Book not found.', 404);
        }

        $model->load(['createdBy:id,name']);
        $model->setRelation('approvedReviews', $model->approvedReviews()->with('createdBy:id,name')->latest()->limit(10)->get());

        return $this->success([
            'book' => BookResource::make($model)->resolve($request),
        ], 'Book detail retrieved successfully.');
    }

    public function getSingleBookData(string $book_slug, Request $request): JsonResponse
    {
        return $this->showBook($book_slug, $request);
    }

    public function bookReviews(string $book, Request $request): JsonResponse
    {
        $model = $this->findPublishedBook($book);

        if (! $model) {
            return $this->error('Book not found.', 404);
        }

        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);
        $payload = BookReviewResource::collection(
            $model->approvedReviews()
                ->with(['book:id,title,slug', 'createdBy:id,name', 'approvedBy:id,name'])
                ->latest()
                ->paginate($perPage)
                ->withQueryString()
        )->response()->getData(true);

        return $this->success($payload, 'Approved reviews retrieved successfully.');
    }

    public function singleBookReviews(string $book_slug, Request $request): JsonResponse
    {
        return $this->bookReviews($book_slug, $request);
    }

    public function myReviews(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);
        $status = (string) $request->input('status', '');

        $query = BookReview::with(['book:id,title,slug', 'createdBy:id,name', 'approvedBy:id,name'])
            ->where('created_by', $user->id)
            ->latest();

        if (in_array($status, BookReview::STATUSES, true)) {
            $query->where('status', $status);
        }

        $payload = BookReviewResource::collection(
            $query->paginate($perPage)->withQueryString()
        )->response()->getData(true);

        return $this->success($payload, 'Your reviews retrieved successfully.');
    }

    public function createBook(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! ($user->isSuperAdmin() || $user->can('books.manage'))) {
            return $this->error('You do not have permission to create books through the API.', 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'ISBN_10' => ['nullable', 'string', 'max:20', Rule::unique('books', 'ISBN_10')->whereNull('deleted_at')],
            'ISBN_13' => ['nullable', 'string', 'max:20', Rule::unique('books', 'ISBN_13')->whereNull('deleted_at')],
            'author' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:20000'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:' . ((int) date('Y') + 1)],
            'status' => ['nullable', Rule::in(Book::STATUSES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = $validator->validated();
        $book = Book::create([
            ...$data,
            'slug' => $this->uniqueBookSlug($data['title']),
            'created_by' => $user->id,
            'status' => $data['status'] ?? Book::STATUS_PUBLISHED,
            'is_seeded' => false,
            'is_protected' => false,
        ]);

        AuditLogger::record('api create book', $book, 'Book created through the JWT API.', [], $book->only(['title', 'slug', 'author', 'category', 'status']), $user, $request);

        return $this->success(['book' => BookResource::make($book->load('createdBy'))->resolve($request)], 'Book created successfully.', 201);
    }

    public function storeBookReview(string $book, Request $request): JsonResponse
    {
        $model = $this->findPublishedBook($book);

        if (! $model) {
            return $this->error('Book not found.', 404);
        }

        return $this->storeReview($request, $model);
    }

    public function createBookReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('book_id'), [
            'book_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $book = $this->findPublishedBook((string) $request->input('book_id'));

        if (! $book) {
            return $this->error('Book not found.', 404);
        }

        return $this->storeReview($request, $book);
    }

    public function updateReview(Request $request, BookReview $review): JsonResponse
    {
        $user = $request->user();

        if (! $this->canManageOwnReviewThroughApi($user, $review)) {
            return $this->error('You can only update your own pending or rejected reviews through the API.', 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string', 'max:5000'],
            'content' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = $validator->validated();
        $payload = [];

        foreach (['rating', 'title'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('body', $data)) {
            $payload['body'] = $data['body'];
            $payload['content'] = $data['body'];
        } elseif (array_key_exists('content', $data)) {
            $payload['body'] = $data['content'];
            $payload['content'] = $data['content'];
        }

        if ($payload === []) {
            return $this->error('No review fields were provided for update.', 422);
        }

        $oldValues = $review->only(['rating', 'title', 'body', 'content', 'status']);
        $review->update($payload);

        AuditLogger::record('api edit review', $review, 'Own review updated through the JWT API.', $oldValues, $review->only(['rating', 'title', 'body', 'content', 'status']), $user, $request);

        return $this->success([
            'review' => BookReviewResource::make($review->fresh(['book', 'createdBy', 'approvedBy']))->resolve($request),
        ], 'Review updated successfully.');
    }

    public function deleteReview(Request $request, BookReview $review): JsonResponse
    {
        $user = $request->user();

        if (! $this->canManageOwnReviewThroughApi($user, $review)) {
            return $this->error('You can only delete your own pending or rejected reviews through the API.', 403);
        }

        $oldValues = $review->only(['book_id', 'created_by', 'rating', 'title', 'status']);
        $review->delete();

        AuditLogger::record('api delete review', $review, 'Own review deleted through the JWT API.', $oldValues, [], $user, $request);

        return $this->success(null, 'Review deleted successfully.');
    }

    private function storeReview(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();

        if (! ($user->isSuperAdmin() || $user->can('reviews.create') || $user->can('reviews.manage'))) {
            return $this->error('You do not have permission to create reviews.', 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required_without:content', 'nullable', 'string', 'max:5000'],
            'content' => ['required_without:body', 'nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $data = $validator->validated();
        $body = $data['body'] ?? $data['content'];

        $review = BookReview::create([
            'book_id' => $book->id,
            'created_by' => $user->id,
            'rating' => $data['rating'],
            'title' => $data['title'],
            'body' => $body,
            'content' => $body,
            'status' => BookReview::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'is_seeded' => false,
            'is_protected' => false,
        ]);

        AuditLogger::record('api create review', $review, 'Review submitted through the JWT API and queued for moderation.', [], $review->only(['book_id', 'created_by', 'rating', 'title', 'status']), $user, $request);

        return $this->success([
            'review' => BookReviewResource::make($review->load(['book', 'createdBy', 'approvedBy']))->resolve($request),
        ], 'Review submitted successfully and is pending moderation.', 201);
    }

    private function findPublishedBook(string $identifier): ?Book
    {
        return Book::published()
            ->where(function (Builder $query) use ($identifier) {
                $query->where('slug', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first();
    }

    private function canManageOwnReviewThroughApi(User $user, BookReview $review): bool
    {
        if ($review->is_protected && ! $user->isSuperAdmin()) {
            return false;
        }

        return (int) $review->created_by === (int) $user->id
            && $user->can('reviews.update-own')
            && in_array($review->status, [BookReview::STATUS_PENDING, BookReview::STATUS_REJECTED], true);
    }

    private function uniqueBookSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'book';
        $slug = $base;
        $counter = 2;

        while (Book::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    private function validationError($validator): JsonResponse
    {
        return $this->error('Validation failed.', 422, [
            'errors' => $validator->errors(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'is_demo' => (bool) $user->is_demo,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'last_login_at' => $user->last_login_at,
        ];
    }
}

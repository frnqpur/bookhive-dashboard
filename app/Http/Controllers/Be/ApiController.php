<?php

namespace App\Http\Controllers\Be;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookReviewResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiController extends Controller
{
    public function users(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $query = User::with(['roles'])->latest();

        if (! $actor->isSuperAdmin()) {
            $query->whereDoesntHave('roles', fn (Builder $roleQuery) => $roleQuery->where('name', User::ROLE_SUPER_ADMIN));
        }

        $this->applySearch($query, $request, ['name', 'email', 'status'], function (Builder $query, string $search) {
            $query->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'like', $search));
        });

        $this->applySort($query, $request, ['id', 'name', 'email', 'status', 'is_demo', 'is_protected', 'created_at', 'updated_at'], 'created_at', 'desc');

        return UserResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function permissions(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Permission::class);

        $query = Permission::query();

        $this->applySearch($query, $request, ['name', 'slug', 'description', 'guard_name']);
        $this->applySort($query, $request, ['id', 'name', 'slug', 'description', 'is_active', 'is_core', 'is_protected', 'guard_name', 'created_at', 'updated_at'], 'created_at', 'desc');

        return PermissionResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function roles(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('name', '!=', User::ROLE_SUPER_ADMIN);
        }

        $this->applySearch($query, $request, ['name', 'slug', 'description', 'guard_name', 'user_type', 'record_access']);
        $this->applySort($query, $request, ['id', 'name', 'slug', 'description', 'is_active', 'is_core', 'is_protected', 'guard_name', 'user_type', 'record_access', 'created_at', 'updated_at'], 'created_at', 'desc');

        return RoleResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function books(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Book::class);

        $query = Book::with('createdBy:id,name');

        if (! $request->user()->can('books.manage')) {
            $query->published();
        }

        if ($request->filled('status') && in_array($request->input('status'), ['draft', 'published'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $this->applySearch($query, $request, ['title', 'slug', 'ISBN_10', 'ISBN_13', 'author', 'category', 'status']);
        $this->applySort($query, $request, ['id', 'title', 'slug', 'ISBN_10', 'ISBN_13', 'author', 'category', 'status', 'average_rating', 'total_reviews', 'created_by', 'created_at', 'updated_at'], 'created_at', 'desc');

        return BookResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function bookReviews(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', BookReview::class);

        $actor = $request->user();
        $query = BookReview::with(['book:id,title,slug', 'createdBy:id,name', 'approvedBy:id,name']);

        if ($request->input('scope') === 'mine') {
            $query->where('created_by', $actor->id);
        } elseif (! ($actor->can('reviews.manage') || $actor->can('reviews.approve'))) {
            $query->where(function (Builder $reviewQuery) use ($actor) {
                $reviewQuery->where('created_by', $actor->id)
                    ->orWhere('status', 'approved');
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', (int) $request->input('book_id'));
        }

        $this->applySearch($query, $request, ['title', 'body', 'content', 'status'], function (Builder $query, string $search) {
            $query->orWhereHas('book', fn (Builder $bookQuery) => $bookQuery->where('title', 'like', $search));
        });
        $this->applySort($query, $request, ['id', 'title', 'rating', 'status', 'book_id', 'created_by', 'approved_by', 'approved_at', 'created_at', 'updated_at'], 'created_at', 'desc');

        return BookReviewResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [5, 10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        return $perPage;
    }

    private function applySort(Builder $query, Request $request, array $allowedFields, string $defaultField, string $defaultOrder): void
    {
        $sortField = $request->input('sort_field', $defaultField);
        $sortOrder = strtolower((string) $request->input('sort_order', $defaultOrder));

        if (! in_array($sortField, $allowedFields, true)) {
            $sortField = $defaultField;
        }

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = $defaultOrder;
        }

        $query->orderBy($sortField, $sortOrder);
    }

    private function applySearch(Builder $query, Request $request, array $fields, ?callable $extraSearch = null): void
    {
        $search = trim((string) $request->input('search', ''));

        if ($search === '') {
            return;
        }

        $search = mb_substr($search, 0, 100);
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';

        $query->where(function (Builder $searchQuery) use ($fields, $like, $extraSearch) {
            foreach ($fields as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $searchQuery->{$method}($field, 'like', $like);
            }

            if ($extraSearch) {
                $extraSearch($searchQuery, $like);
            }
        });
    }
}

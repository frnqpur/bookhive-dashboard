<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\BookReviewResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $canManageReviews = $user->can('reviews.manage') || $user->can('reviews.approve');
        $canManageUsers = $user->can('users.manage');
        $canViewAuditLogs = $user->can('audit-logs.view');

        $reviewQuery = BookReview::query();
        if (! $canManageReviews) {
            $reviewQuery->where(function (Builder $query) use ($user) {
                $query->where('status', BookReview::STATUS_APPROVED)
                    ->orWhere('created_by', $user->id);
            });
        }

        $bookQuery = Book::query()->when(! $user->can('books.manage'), fn ($query) => $query->published());

        $latestBooks = (clone $bookQuery)
            ->with('createdBy:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $latestReviews = (clone $reviewQuery)
            ->with(['book:id,title,slug', 'createdBy:id,name', 'approvedBy:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingReviews = $canManageReviews
            ? BookReview::with(['book:id,title,slug', 'createdBy:id,name'])
                ->where('status', BookReview::STATUS_PENDING)
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $latestUsers = $canManageUsers
            ? User::with('roles:id,name')->latest()->limit(5)->get()
            : collect();

        $latestActivities = $canViewAuditLogs
            ? AuditLog::with('user:id,name,email')->latest()->limit(6)->get()->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'entity_type' => class_basename((string) ($log->entity_type ?: $log->auditable_type)),
                'entity_id' => $log->entity_id ?: $log->auditable_id,
                'user' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at ? $log->created_at->timezone(config('app.timezone'))->diffForHumans() . ' (WIB)' : null,
            ])
            : collect();

        return Inertia::render('Dashboard', [
            'stats' => $this->stats($user, clone $bookQuery, clone $reviewQuery),
            'latestBooks' => BookResource::collection($latestBooks),
            'latestReviews' => BookReviewResource::collection($latestReviews),
            'latestUsers' => UserResource::collection($latestUsers),
            'pendingReviews' => BookReviewResource::collection($pendingReviews),
            'latestActivities' => $latestActivities,
            'quickActions' => $this->quickActions($user),
            'chartData' => [
                'reviewsPerMonth' => $this->reviewsPerMonth(clone $reviewQuery),
                'booksByCategory' => $this->booksByCategory(clone $bookQuery),
                'usersByRole' => $this->usersByRole($canManageUsers),
                'reviewStatusDistribution' => $this->reviewStatusDistribution(clone $reviewQuery),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stats(User $user, Builder $bookQuery, Builder $reviewQuery): array
    {
        return [
            [
                'name' => 'Total Users',
                'stat' => $user->can('users.manage') ? User::count() : 1,
                'description' => $user->can('users.manage') ? 'Registered dashboard accounts' : 'Your active dashboard account',
                'tone' => 'indigo',
            ],
            [
                'name' => 'Total Books',
                'stat' => $bookQuery->count(),
                'description' => 'Books visible in the BookHive catalog',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Total Reviews',
                'stat' => (clone $reviewQuery)->count(),
                'description' => 'Reviews visible to your role',
                'tone' => 'slate',
            ],
            [
                'name' => 'Pending Reviews',
                'stat' => (clone $reviewQuery)->where('status', BookReview::STATUS_PENDING)->count(),
                'description' => 'Reviews waiting for moderation',
                'tone' => 'amber',
            ],
            [
                'name' => 'Approved Reviews',
                'stat' => (clone $reviewQuery)->where('status', BookReview::STATUS_APPROVED)->count(),
                'description' => 'Reviews counted in public ratings',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Rejected Reviews',
                'stat' => (clone $reviewQuery)->where('status', BookReview::STATUS_REJECTED)->count(),
                'description' => 'Reviews hidden from public book pages',
                'tone' => 'rose',
            ],
            [
                'name' => 'Total Admins',
                'stat' => User::role(User::ROLE_ADMIN)->count(),
                'description' => 'Admin demo and registered accounts',
                'tone' => 'indigo',
            ],
            [
                'name' => 'Total Editors',
                'stat' => User::role(User::ROLE_EDITOR)->count(),
                'description' => 'Book catalog manager accounts',
                'tone' => 'emerald',
            ],
            [
                'name' => 'Total Reviewers',
                'stat' => User::role(User::ROLE_REVIEWER)->count(),
                'description' => 'Review-focused dashboard accounts',
                'tone' => 'amber',
            ],
            [
                'name' => 'Total Customers',
                'stat' => User::role(User::ROLE_CUSTOMER)->count(),
                'description' => 'Customer demo and registered accounts',
                'tone' => 'slate',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function reviewsPerMonth(Builder $query): array
    {
        return collect(range(5, 0))->map(function (int $monthsAgo) use ($query) {
            $month = Carbon::now()->subMonths($monthsAgo)->startOfMonth();

            return [
                'label' => $month->format('M'),
                'value' => (clone $query)
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count(),
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function booksByCategory(Builder $query): array
    {
        return $query
            ->select('category')
            ->get()
            ->groupBy(fn (Book $book) => $book->category ?: 'Uncategorized')
            ->map(fn ($books, $category) => [
                'label' => (string) $category,
                'value' => $books->count(),
            ])
            ->sortByDesc('value')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function usersByRole(bool $canManageUsers): array
    {
        if (! $canManageUsers) {
            return [];
        }

        return collect([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN,
            User::ROLE_EDITOR,
            User::ROLE_REVIEWER,
            User::ROLE_CUSTOMER,
        ])->map(fn (string $role) => [
            'label' => $role,
            'value' => User::role($role)->count(),
        ])->values()->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function reviewStatusDistribution(Builder $query): array
    {
        return collect(BookReview::STATUSES)->map(fn (string $status) => [
            'label' => ucfirst($status),
            'value' => (clone $query)->where('status', $status)->count(),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function quickActions(User $user): array
    {
        return collect([
            ['label' => 'Add book', 'description' => 'Create a new book record', 'href' => route('dashboard.be.books.create'), 'can' => $user->can('books.manage')],
            ['label' => 'Write review', 'description' => 'Submit a pending review', 'href' => route('dashboard.be.bookReviews.create'), 'can' => $user->can('reviews.create') || $user->can('reviews.manage')],
            ['label' => 'Moderate reviews', 'description' => 'Approve or reject pending reviews', 'href' => route('dashboard.be.bookReviews.moderation'), 'can' => $user->can('reviews.approve')],
            ['label' => 'Manage users', 'description' => 'Create or update dashboard users', 'href' => route('dashboard.global.users.list'), 'can' => $user->can('users.manage')],
            ['label' => 'Audit logs', 'description' => 'Review important activity', 'href' => route('dashboard.auditLogs.index'), 'can' => $user->can('audit-logs.view')],
            ['label' => 'Demo reset', 'description' => 'Restore protected public demo data', 'href' => route('dashboard.demoReset.index'), 'can' => $user->can('demo-reset.manage')],
        ])->filter(fn (array $item) => $item['can'])->map(fn (array $item) => [
            'label' => $item['label'],
            'description' => $item['description'],
            'href' => $item['href'],
        ])->values()->all();
    }
}

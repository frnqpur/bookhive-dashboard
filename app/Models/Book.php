<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_PUBLISHED];

    protected $fillable = [
        'title',
        'slug',
        'ISBN_10',
        'ISBN_13',
        'author',
        'category',
        'cover_image',
        'description',
        'published_year',
        'status',
        'average_rating',
        'total_reviews',
        'created_by',
        'is_seeded',
        'is_protected',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'published_year' => 'integer',
        'is_seeded' => 'boolean',
        'is_protected' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function bookReviews(): HasMany
    {
        return $this->hasMany(BookReview::class)->with(['createdBy']);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(BookReview::class)->where('status', BookReview::STATUS_APPROVED);
    }

    public function visibleReviewsFor(?User $actor): HasMany
    {
        $query = $this->hasMany(BookReview::class);

        if (! $actor) {
            return $query->where('status', BookReview::STATUS_APPROVED);
        }

        if ($actor->isSuperAdmin() || $actor->can('reviews.manage') || $actor->can('reviews.approve')) {
            return $query;
        }

        return $query->where(function (Builder $reviewQuery) use ($actor) {
            $reviewQuery->where('status', BookReview::STATUS_APPROVED)
                ->orWhere('created_by', $actor->id);
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return Storage::url($this->cover_image);
    }

    public function getBookReviewsAvgRatingAttribute($value): string
    {
        $rating = round((float) $value, 1);
        return "{$rating}/5";
    }


    public function refreshReviewStats(): void
    {
        $stats = $this->reviews()
            ->where('status', BookReview::STATUS_APPROVED)
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as rating_average')
            ->first();

        $this->forceFill([
            'average_rating' => round((float) ($stats?->rating_average ?? 0), 2),
            'total_reviews' => (int) ($stats?->reviews_count ?? 0),
        ])->saveQuietly();
    }

    public function canBeViewedBy(?User $actor): bool
    {
        if ($this->status === self::STATUS_PUBLISHED) {
            return true;
        }

        return $actor && ($actor->isSuperAdmin() || $actor->can('books.manage'));
    }

    public function canBeModifiedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('books.manage');
    }

    public function canBeDeletedBy(?User $actor): bool
    {
        return $this->canBeModifiedBy($actor);
    }
}

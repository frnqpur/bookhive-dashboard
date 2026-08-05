<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookReview extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUSES = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];

    protected $fillable = [
        'book_id',
        'created_by',
        'rating',
        'title',
        'body',
        'content',
        'status',
        'moderation_note',
        'approved_by',
        'approved_at',
        'is_seeded',
        'is_protected',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
        'is_seeded' => 'boolean',
        'is_protected' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (BookReview $review): void {
            $review->book?->refreshReviewStats();
        });

        static::deleted(function (BookReview $review): void {
            $review->book?->refreshReviewStats();
        });

        static::restored(function (BookReview $review): void {
            $review->book?->refreshReviewStats();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


    public function canBeViewedBy(?User $actor): bool
    {
        if ($this->status === self::STATUS_APPROVED) {
            return true;
        }

        if (! $actor) {
            return false;
        }

        return $actor->isSuperAdmin()
            || $actor->can('reviews.manage')
            || $actor->can('reviews.approve')
            || (int) $actor->id === (int) $this->created_by;
    }

    public function canBeModeratedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('reviews.approve');
    }

    public function canBeModifiedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($actor->isSuperAdmin() || $actor->can('reviews.manage')) {
            return true;
        }

        return (int) $actor->id === (int) $this->created_by
            && $actor->can('reviews.update-own')
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED], true);
    }

    public function canBeDeletedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin()
            || $actor->can('reviews.manage')
            || (
                (int) $actor->id === (int) $this->created_by
                && $actor->can('reviews.update-own')
                && in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED], true)
            );
    }
}

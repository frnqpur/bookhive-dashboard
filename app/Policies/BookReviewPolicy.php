<?php

namespace App\Policies;

use App\Models\BookReview;
use App\Models\User;

class BookReviewPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('reviews.view') || $actor->can('reviews.create') || $actor->can('reviews.manage') || $actor->can('reviews.approve');
    }

    public function view(User $actor, BookReview $review): bool
    {
        return $actor->can('reviews.manage')
            || $actor->can('reviews.approve')
            || ($actor->can('reviews.view') && $review->status === 'approved')
            || ($actor->id === $review->created_by && $actor->can('reviews.update-own'));
    }

    public function create(User $actor): bool
    {
        return $actor->can('reviews.create') || $actor->can('reviews.manage');
    }

    public function update(User $actor, BookReview $review): bool
    {
        return $review->canBeModifiedBy($actor) || $review->canBeModeratedBy($actor);
    }

    public function moderate(User $actor, BookReview $review): bool
    {
        return $review->canBeModeratedBy($actor);
    }

    public function delete(User $actor, BookReview $review): bool
    {
        return $review->canBeDeletedBy($actor);
    }
}

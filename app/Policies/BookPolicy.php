<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('books.view') || $actor->can('books.manage');
    }

    public function view(User $actor, Book $book): bool
    {
        return $actor->can('books.manage') || ($actor->can('books.view') && $book->status === 'published');
    }

    public function create(User $actor): bool
    {
        return $actor->can('books.manage');
    }

    public function update(User $actor, Book $book): bool
    {
        return $book->canBeModifiedBy($actor);
    }

    public function delete(User $actor, Book $book): bool
    {
        return $book->canBeDeletedBy($actor);
    }
}

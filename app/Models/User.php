<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_EDITOR = 'Editor';
    public const ROLE_REVIEWER = 'Reviewer';
    public const ROLE_CUSTOMER = 'Customer';

    public const PUBLIC_REGISTER_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_EDITOR,
        self::ROLE_REVIEWER,
        self::ROLE_CUSTOMER,
    ];

    public const ROLE_DESCRIPTIONS = [
        self::ROLE_ADMIN => 'Can manage users, roles, permissions, books, reviews, settings, and most admin workflows except Super Admin ownership.',
        self::ROLE_EDITOR => 'Can manage the book catalog and moderate reviews, but cannot manage users, roles, permissions, or settings.',
        self::ROLE_REVIEWER => 'Can access books, create reviews, and manage review workflows without administrative user or permission access.',
        self::ROLE_CUSTOMER => 'Can explore books, create personal reviews, and update their own profile with customer-level access.',
    ];

    public const DEMO_CREDENTIALS = [
        ['role' => self::ROLE_ADMIN, 'email' => 'admin@demo.com', 'password' => 'password'],
        ['role' => self::ROLE_EDITOR, 'email' => 'editor@demo.com', 'password' => 'password'],
        ['role' => self::ROLE_REVIEWER, 'email' => 'reviewer@demo.com', 'password' => 'password'],
        ['role' => self::ROLE_CUSTOMER, 'email' => 'customer@demo.com', 'password' => 'password'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_protected',
        'is_demo',
        'protected_reason',
        'created_by',
        'last_login_at',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_protected' => 'boolean',
        'is_demo' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookReview::class, 'created_by');
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(BookReview::class, 'approved_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isDemoUser(): bool
    {
        return (bool) $this->is_demo;
    }

    public function isProtectedUser(): bool
    {
        return (bool) $this->is_protected;
    }

    public function canBeDeletedBy(?User $actor): bool
    {
        if (! $actor || $this->is_protected || $this->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || ($actor->can('users.manage') && ! $this->isSuperAdmin());
    }

    public function canHaveEmailChangedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->isDemoUser() && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->id === $this->id || $actor->isSuperAdmin() || $actor->can('users.manage');
    }

    public function canHavePasswordChangedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->isDemoUser() && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->id === $this->id || $actor->isSuperAdmin() || $actor->can('users.manage');
    }

    public function canHaveRoleChangedBy(?User $actor, ?string $targetRole = null): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($this->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($targetRole === self::ROLE_SUPER_ADMIN && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('users.manage');
    }

    public function canBeActivatedOrDisabledBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || ($actor->can('users.manage') && ! $this->isSuperAdmin());
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}

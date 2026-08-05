<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actor = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->getRoleNames()->values(),
            'status' => $this->status,
            'is_demo' => (bool) $this->is_demo,
            'is_protected' => (bool) $this->is_protected,
            'protected_reason' => $this->protected_reason,
            'created_by' => $this->created_by,
            'last_login_at' => $this->last_login_at,
            'can_edit' => $actor ? $actor->can('update', $this->resource) : false,
            'can_delete' => $actor ? $this->canBeDeletedBy($actor) : false,
            'created_at' => $this->formatWibDate('created_at'),
            'updated_at' => $this->formatWibDate('updated_at'),
        ];
    }
    private function formatWibDate(?string $attribute): ?string
    {
        if (! $attribute) {
            return null;
        }

        $value = $this->resource?->getRawOriginal($attribute) ?? null;

        if (! $value) {
            return null;
        }

        return Carbon::parse($value)
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->format('D, M j, Y g:i A') . ' WIB';
    }
}

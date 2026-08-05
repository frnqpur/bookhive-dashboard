<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PermissionResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'is_core' => (bool) $this->is_core,
            'is_protected' => (bool) $this->is_protected,
            'protected_reason' => $this->protected_reason,
            'guard_name' => $this->guard_name,
            'can_edit' => $actor ? $this->canBeModifiedBy($actor) : false,
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

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actor = $request->user();
        $body = (string) ($this->body ?? $this->content ?? '');

        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book' => $this->whenLoaded('book', fn () => $this->book?->title),
            'book_slug' => $this->whenLoaded('book', fn () => $this->book?->slug),
            'created_by' => $this->created_by,
            'created_by_user' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'rating' => $this->rating,
            'rating_label' => $this->rating ? $this->rating . '/5' : '-',
            'title' => $this->title,
            'body' => $body,
            'body_excerpt' => Str::words($body, 18),
            'content' => $body,
            'status' => $this->status,
            'moderation_note' => $this->moderation_note,
            'approved_by' => $this->approved_by,
            'approved_by_user' => $this->whenLoaded('approvedBy', fn () => $this->approvedBy?->name),
            'approved_at' => $this->formatWibDate('approved_at'),
            'is_seeded' => (bool) $this->is_seeded,
            'is_protected' => (bool) $this->is_protected,
            'can_view' => $this->canBeViewedBy($actor),
            'can_edit' => $actor ? ($this->canBeModifiedBy($actor) || $this->canBeModeratedBy($actor)) : false,
            'can_delete' => $actor ? $this->canBeDeletedBy($actor) : false,
            'can_approve' => $actor ? $this->canBeModeratedBy($actor) : false,
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

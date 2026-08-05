<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $actor = $request->user();
        $description = (string) ($this->description ?? '');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'ISBN_10' => $this->ISBN_10,
            'ISBN_13' => $this->ISBN_13,
            'author' => $this->author,
            'category' => $this->category,
            'cover_image' => $this->cover_image,
            'cover_url' => $this->cover_url,
            'description' => $this->description,
            'description_excerpt' => Str::words($description, 24),
            'published_year' => $this->published_year,
            'status' => $this->status,
            'average_rating' => (float) $this->average_rating,
            'rating_label' => number_format((float) $this->average_rating, 1) . '/5',
            'total_reviews' => (int) $this->total_reviews,
            'created_by' => $this->created_by,
            'created_by_user' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'approved_reviews' => $this->whenLoaded('approvedReviews', fn () => BookReviewResource::collection($this->approvedReviews)),
            'is_seeded' => (bool) $this->is_seeded,
            'is_protected' => (bool) $this->is_protected,
            'can_view' => $this->canBeViewedBy($actor),
            'can_edit' => $actor ? $this->canBeModifiedBy($actor) : false,
            'can_delete' => $actor ? $this->canBeDeletedBy($actor) : false,
            'show_url' => $this->id ? route('dashboard.be.books.show', $this->id, false) : null,
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

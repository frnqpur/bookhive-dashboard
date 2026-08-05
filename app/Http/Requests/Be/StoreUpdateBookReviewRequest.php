<?php

namespace App\Http\Requests\Be;

use App\Models\BookReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateBookReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isSuperAdmin()
            || $user?->can('reviews.manage')
            || $user?->can('reviews.create')
            || $user?->can('reviews.update-own')
            || $user?->can('reviews.approve');
    }

    public function rules(): array
    {
        $reviewId = $this->route('id') ?: null;

        return [
            'book_id' => [$reviewId ? 'nullable' : 'required', 'integer', Rule::exists('books', 'id')->where('status', 'published')->whereNull('deleted_at')],
            'rating' => [$reviewId ? 'nullable' : 'required', 'integer', 'min:1', 'max:5'],
            'title' => [$reviewId ? 'nullable' : 'required', 'string', 'max:255'],
            'body' => [$reviewId ? 'nullable' : 'required', 'string', 'max:5000'],
            'content' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'string', Rule::in(BookReview::STATUSES)],
            'moderation_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Please select a published book for this review.',
            'book_id.exists' => 'Please select a published book for this review.',
        ];
    }
}

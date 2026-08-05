<?php

namespace App\Http\Requests\Be;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('books.manage');
    }

    public function rules(): array
    {
        $bookId = $this->route('id') ?: null;
        $maxYear = (int) date('Y') + 1;

        return [
            'title' => ['required', 'string', 'max:255'],
            'ISBN_10' => ['nullable', 'string', 'max:20', Rule::unique('books', 'ISBN_10')->ignore($bookId)->whereNull('deleted_at')],
            'ISBN_13' => ['nullable', 'string', 'max:20', Rule::unique('books', 'ISBN_13')->ignore($bookId)->whereNull('deleted_at')],
            'author' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:20000'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:' . $maxYear],
            'status' => ['nullable', 'string', Rule::in(Book::STATUSES)],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for article listing with validation.
 */
class ArticleIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'source' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:100'],
            'author' => ['sometimes', 'string', 'max:255'],
            'from' => ['sometimes', 'date', 'before_or_equal:to'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get the validated filters for the repository.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return array_filter([
            'q' => $this->input('q'),
            'source' => $this->input('source'),
            'category' => $this->input('category'),
            'author' => $this->input('author'),
            'from' => $this->input('from'),
            'to' => $this->input('to'),
        ], fn($value) => $value !== null);
    }

    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}

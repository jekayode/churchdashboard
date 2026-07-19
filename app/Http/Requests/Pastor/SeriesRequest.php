<?php

declare(strict_types=1);

namespace App\Http\Requests\Pastor;

use App\Models\Series;
use Illuminate\Foundation\Http\FormRequest;

final class SeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $series = $this->route('series');

        return $series instanceof Series
            ? $this->user()?->can('update', $series) ?? false
            : $this->user()?->can('create', Series::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tone' => ['nullable', 'in:orange,purple,amber,lemon'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_published' => ['sometimes', 'boolean'],
            'cover' => ['nullable', 'image', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_on.after_or_equal' => 'The end date must not be before the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }
}

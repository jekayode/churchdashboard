<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateChangelogEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDirectoryAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'version' => ['sometimes', 'string', 'max:50'],
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

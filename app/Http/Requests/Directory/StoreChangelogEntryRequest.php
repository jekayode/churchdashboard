<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChangelogEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDirectoryAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

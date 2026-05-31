<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDirectoryAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'link' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['boolean'],
            'is_dismissible' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

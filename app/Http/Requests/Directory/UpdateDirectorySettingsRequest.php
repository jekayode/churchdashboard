<?php

declare(strict_types=1);

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateDirectorySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDirectoryAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'tagline' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'announcement_title' => ['nullable', 'string', 'max:255'],
            'announcement_body' => ['nullable', 'string'],
            'announcement_link' => ['nullable', 'url', 'max:500'],
            'announcement_active' => ['boolean'],
            'announcement_dismissible' => ['boolean'],
            'reviews_require_approval' => ['boolean'],
            'business_approval_required' => ['boolean'],
        ];
    }
}

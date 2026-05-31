<?php

declare(strict_types=1);

namespace App\Http\Requests\Builders;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBuilderSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBuilders() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'whatsapp_group_link' => ['nullable', 'url', 'max:500'],
            'google_drive_link' => ['nullable', 'url', 'max:500'],
            'intro_text' => ['nullable', 'string', 'max:2000'],
            'confirmation_body' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Builders;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBuilderResourceRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}

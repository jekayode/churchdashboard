<?php

declare(strict_types=1);

namespace App\Http\Requests\Pastor;

use App\Models\Sermon;
use Illuminate\Foundation\Http\FormRequest;

final class SermonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sermon = $this->route('sermon');

        return $sermon instanceof Sermon
            ? $this->user()?->can('update', $sermon) ?? false
            : $this->user()?->can('create', Sermon::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'speaker' => ['required', 'string', 'max:255'],
            'speaker_member_id' => ['nullable', 'integer', 'exists:members,id'],
            'series_id' => ['nullable', 'integer', 'exists:series,id'],
            'preached_on' => ['required', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'tone' => ['nullable', 'in:orange,purple,amber,lemon'],
            'is_live' => ['sometimes', 'boolean'],
            // required_if_accepted, not required_if:is_live,1 — prepareForValidation
            // casts is_live to a real boolean, which never matches the string "1".
            'live_url' => ['nullable', 'url', 'max:2048', 'required_if_accepted:is_live'],
            'is_published' => ['sometimes', 'boolean'],

            // Media. Recording and slides can be large, hence the raised limits.
            'recording' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/x-m4a,video/mp4', 'max:512000'],
            'cover' => ['nullable', 'image', 'max:8192'],
            'slides' => ['nullable', 'array'],
            'slides.*' => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:20480'],

            // Passages, in display order.
            'passages' => ['nullable', 'array'],
            'passages.*.reference' => ['required_with:passages', 'string', 'max:255'],
            'passages.*.book' => ['nullable', 'string', 'max:100'],
            'passages.*.chapter' => ['nullable', 'integer', 'min:1', 'max:150'],
            'passages.*.verses' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'live_url.required_if_accepted' => 'A stream URL is required when the sermon is marked live.',
            'recording.mimetypes' => 'The recording must be an audio file (mp3, m4a, wav) or an mp4 video.',
            'recording.max' => 'The recording may not be larger than 500MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_live' => $this->boolean('is_live'),
            'is_published' => $this->boolean('is_published'),
        ]);
    }
}

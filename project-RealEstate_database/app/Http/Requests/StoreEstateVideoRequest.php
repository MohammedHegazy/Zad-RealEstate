<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: رفع فيديو جولة لعقار
// =============================================================================
// mimetypes تحدد صيغ الفيديو المقبولة؛ max من config.

use Illuminate\Foundation\Http\FormRequest;

class StoreEstateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm',
                'max:'.config('realestate.upload.max_video_kb'),
            ],
        ];
    }
}

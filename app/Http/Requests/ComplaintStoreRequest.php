<?php

namespace App\Http\Requests;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ComplaintStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Complaint::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'complaint_category_id' => ['required', 'exists:complaint_categories,id'],
            'description' => ['required', 'string', 'min:20'],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'attachments.*' => [
                File::types(['jpg', 'jpeg', 'png', 'pdf'])->max('5mb'),
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    Complaint::STATUS_PENDING,
                    Complaint::STATUS_IN_PROGRESS,
                    Complaint::STATUS_RESOLVED,
                    Complaint::STATUS_REJECTED,
                ])
            ],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'public_message' => ['nullable', 'string'],
            'internal_note' => ['nullable', 'string'],
        ];
    }
}

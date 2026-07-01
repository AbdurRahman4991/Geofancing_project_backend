<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:employees,employee_id',
            'company_id' => 'nullable|exists:companies,id',

            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,terminated',
            'nature_of_employment' => 'required|string',

            'department' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'reporting_person' => 'nullable|string|max:255',

            'date_of_joining' => 'required|date',

            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'section_info' => 'nullable|string|max:255',
        ];
    }
}

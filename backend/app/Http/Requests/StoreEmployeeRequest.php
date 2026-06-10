<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Controlled by authorization routes middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'user_id' => 'nullable|uuid|unique:users,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'dob' => 'nullable|date|before:today',
            'marital_status' => 'nullable|string|in:Single,Married,Divorced,Widowed',
            'joining_date' => 'required|date',
            'status' => 'sometimes|required|string|in:Active,Probation,Suspended,Terminated',
            'department_id' => 'nullable|uuid',
            'designation_id' => 'nullable|uuid',

            // JSON validations
            'personal_info' => 'nullable|array',
            'personal_info.passport_number' => 'nullable|string|max:50',
            'personal_info.national_id' => 'nullable|string|max:50',
            'personal_info.address' => 'nullable|string',

            'family_info' => 'nullable|array',
            'family_info.*.name' => 'required_with:family_info|string|max:100',
            'family_info.*.relationship' => 'required_with:family_info|string|max:50',
            'family_info.*.dob' => 'nullable|date',

            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string|max:100',
            'emergency_contacts.*.relationship' => 'required_with:emergency_contacts|string|max:50',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string|max:50',

            'bank_details' => 'nullable|array',
            'bank_details.bank_name' => 'required_with:bank_details|string|max:150',
            'bank_details.account_number' => 'required_with:bank_details|string|max:50',
            'bank_details.branch_code' => 'nullable|string|max:50',
            'bank_details.swift_code' => 'nullable|string|max:50',
        ];
    }
}

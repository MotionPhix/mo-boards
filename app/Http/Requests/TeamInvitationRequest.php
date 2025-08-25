<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Policies\CompanyTeamPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TeamInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('invite', [CompanyTeamPolicy::class, $this->user()->currentCompany]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['company_owner', 'manager', 'editor', 'viewer'])],
        ];
    }
}

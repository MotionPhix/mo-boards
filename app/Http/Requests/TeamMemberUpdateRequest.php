<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Policies\CompanyTeamPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TeamMemberUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [CompanyTeamPolicy::class, $this->user()->currentCompany, $this->route('member')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['admin', 'manager', 'member', 'viewer'])],
        ];
    }
}

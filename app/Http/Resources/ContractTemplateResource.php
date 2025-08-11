<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'content' => $this->content,
            'price' => $this->price,
            'formatted_price' => $this->price ? number_format($this->price, 2) : null,
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium ?? false,
            'is_system_template' => $this->is_system_template ?? false,
            'category' => $this->category,
            'tags' => $this->tags ? (is_array($this->tags) ? $this->tags : json_decode($this->tags, true)) : [],
            'default_terms' => $this->default_terms ?? [],
            'custom_fields' => $this->custom_fields ?? [],
            'features' => $this->features,
            'preview_image' => $this->preview_image,
            'company' => $this->when(
                $this->relationLoaded('company') && $this->company,
                function () {
                    return [
                        'id' => $this->company->id,
                        'uuid' => $this->company->uuid,
                        'name' => $this->company->name,
                    ];
                }
            ),
            'contracts_count' => $this->when(
                isset($this->contracts_count),
                $this->contracts_count
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'actions' => [
                'can_view' => true,
                'can_edit' => $this->company_id === $request->user()?->currentCompany?->id,
                'can_delete' => $this->company_id === $request->user()?->currentCompany?->id,
                'can_duplicate' => true,
                'can_purchase' => $this->is_system_template && $this->company_id !== $request->user()?->currentCompany?->id,
            ],
        ];
    }
}

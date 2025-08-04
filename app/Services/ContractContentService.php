<?php

namespace App\Services;

use App\Models\Contract;

class ContractContentService
{
    /**
     * Process a contract's design content by replacing placeholders with actual values
     *
     * @param Contract $contract
     * @return string The processed content
     */
    public function processContractContent(Contract $contract)
    {
        // Start with the design content (contains placeholders)
        $processedContent = $contract->design;

        if (empty($processedContent)) {
            return '';
        }

        // Replace standard placeholders
        $standardReplacements = [
            '{{contract_id}}' => $contract->contract_number,
            '{{contract_date}}' => $contract->created_at->format('F j, Y'),
            '{{client_name}}' => $contract->client_name,
            '{{client_email}}' => $contract->client_email,
            '{{client_phone}}' => $contract->client_phone,
            '{{client_address}}' => $contract->client_address,
            '{{client_company}}' => $contract->client_company,
            '{{contract_amount}}' => number_format($contract->total_amount, 2),
            '{{monthly_amount}}' => number_format($contract->monthly_amount, 2),
            '{{start_date}}' => $contract->start_date ? date('F j, Y', strtotime($contract->start_date)) : '',
            '{{end_date}}' => $contract->end_date ? date('F j, Y', strtotime($contract->end_date)) : '',
        ];

        // Add company information if available
        if ($contract->company) {
            $standardReplacements = array_merge($standardReplacements, [
                '{{company_name}}' => $contract->company->name,
                '{{company_email}}' => $contract->company->email,
                '{{company_phone}}' => $contract->company->phone,
                '{{company_address}}' => $contract->company->address,
            ]);
        }

        // Replace standard placeholders
        foreach ($standardReplacements as $placeholder => $value) {
            $processedContent = str_replace($placeholder, $value, $processedContent);
        }

        // Replace custom field placeholders
        if (!empty($contract->custom_field_values)) {
            foreach ($contract->custom_field_values as $field) {
                $placeholder = '{{' . $field['id'] . '}}';
                $processedContent = str_replace($placeholder, $field['value'], $processedContent);
            }
        }

        // Store processed content in the contract
        $contract->content = $processedContent;
        $contract->save();

        return $processedContent;
    }

    /**
     * Copy template content to contract design when creating a new contract
     *
     * @param Contract $contract
     * @param \App\Models\ContractTemplate $template
     * @return Contract
     */
    public function applyTemplateToContract(Contract $contract, $template)
    {
        // Copy template content to contract design
        $contract->design = $template->content;

        // Process the content right away to generate the initial content version
        $this->processContractContent($contract);

        return $contract;
    }
}

<?php

namespace App\Services;

use App\Models\ContractTemplate;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ContractTemplateService
{
    /**
     * Get minimal template lists for the Contract Edit page (owned + purchased)
     */
    public function getTemplatesForEdit(?Request $request = null): array
    {
        $company = Auth::user()->currentCompany;

        $ownedTemplates = ContractTemplate::where('company_id', $company->id)
            ->active()
            ->get(['id', 'name', 'description', 'content', 'is_active', 'created_at', 'updated_at']);

        $purchasedTemplates = ContractTemplate::systemTemplates()
            ->active()
            ->whereHas('purchasedBy', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->get(['id', 'name', 'description', 'content', 'is_active', 'created_at', 'updated_at']);

        return [
            'owned' => $ownedTemplates,
            'purchased' => $purchasedTemplates,
        ];
    }
    /**
     * Get paginated templates with filters for the index page
     */
    public function getTemplatesForIndex(Request $request): array
    {
        $company = Auth::user()->currentCompany;
        $perPage = $request->get('per_page', 12);
        $search = $request->get('search');
        $category = $request->get('category');
        $status = $request->get('status'); // active, inactive, all
        $type = $request->get('type'); // company, purchased, available, all
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        // Get company's own templates
        $companyTemplates = $this->getCompanyTemplates($company->id, $search, $category, $status, $sort, $direction);

        // Get purchased system templates
        $purchasedTemplates = $this->getPurchasedTemplates($company->id, $search, $category, $status, $sort, $direction);

        // Get available system templates (not purchased)
        $availableSystemTemplates = $this->getAvailableSystemTemplates($company->id, $search, $category, $status, $sort, $direction);

        // Combine and paginate based on type filter
        $allTemplates = collect();

        if (!$type || $type === 'all' || $type === 'company') {
            $allTemplates = $allTemplates->concat($companyTemplates);
        }

        if (!$type || $type === 'all' || $type === 'purchased') {
            $allTemplates = $allTemplates->concat($purchasedTemplates);
        }

        if (!$type || $type === 'all' || $type === 'available') {
            $allTemplates = $allTemplates->concat($availableSystemTemplates);
        }

        // Sort the combined collection
        $allTemplates = $this->sortTemplates($allTemplates, $sort, $direction);

        // Paginate the results
        $total = $allTemplates->count();
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allTemplates->slice($offset, $perPage)->values();

        // Transform items into plain arrays to avoid using HTTP Resources in services
        $transformedItems = $items->map(function ($t) {
            return [
                'id' => $t->id,
                'uuid' => $t->uuid,
                'name' => $t->name,
                'description' => $t->description,
                'content' => $t->content,
                'price' => $t->price,
                'formatted_price' => $t->price ? number_format($t->price, 2) : null,
                'is_active' => $t->is_active,
                'is_premium' => $t->is_premium ?? false,
                'is_system_template' => $t->is_system_template ?? false,
                'category' => $t->category,
                'tags' => $t->tags ? (is_array($t->tags) ? $t->tags : json_decode($t->tags, true)) : [],
                'default_terms' => $t->default_terms ?? [],
                'custom_fields' => $t->custom_fields ?? [],
                'features' => $t->features,
                'preview_image' => $t->preview_image,
                'contracts_count' => isset($t->contracts_count) ? $t->contracts_count : null,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ];
        });

        // Create pagination structure manually to match Laravel's format
        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;
        $lastPage = (int) ceil($total / $perPage);

        // Generate pagination links
        $baseUrl = $request->url();
        $query = $request->query();

        $links = [];

        // Previous link
        $links[] = [
            'url' => $currentPage > 1 ? $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $currentPage - 1])) : null,
            'label' => '&laquo; Previous',
            'active' => false
        ];

        // Page number links
        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $i])),
                'label' => (string) $i,
                'active' => $i === $currentPage
            ];
        }

        // Next link
        $links[] = [
            'url' => $currentPage < $lastPage ? $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $currentPage + 1])) : null,
            'label' => 'Next &raquo;',
            'active' => false
        ];

        $paginatedData = [
            'data' => $transformedItems,
            'current_page' => $currentPage,
            'first_page_url' => $baseUrl . '?' . http_build_query(array_merge($query, ['page' => 1])),
            'from' => $from,
            'last_page' => $lastPage,
            'last_page_url' => $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $lastPage])),
            'links' => $links,
            'next_page_url' => $currentPage < $lastPage ? $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $currentPage + 1])) : null,
            'path' => $baseUrl,
            'per_page' => $perPage,
            'prev_page_url' => $currentPage > 1 ? $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $currentPage - 1])) : null,
            'to' => $to,
            'total' => $total,
            'meta' => [
                'current_page' => $currentPage,
                'from' => $from,
                'last_page' => $lastPage,
                'links' => $links,
                'path' => $baseUrl,
                'per_page' => $perPage,
                'to' => $to,
                'total' => $total
            ]
        ];

        return [
            'templates' => $paginatedData,
            'stats' => $this->getTemplateStats($companyTemplates, $purchasedTemplates, $availableSystemTemplates),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
                'type' => $type,
                'sort' => $sort,
                'direction' => $direction,
            ]
        ];
    }

    /**
     * Get company's own templates
     */
    private function getCompanyTemplates($companyId, $search, $category, $status, $sort, $direction)
    {
        $query = ContractTemplate::where('company_id', $companyId)
            ->withCount('contracts');

        $query = $this->applyFilters($query, $search, $category, $status);

        return $query->get();
    }

    /**
     * Get purchased system templates
     */
    private function getPurchasedTemplates($companyId, $search, $category, $status, $sort, $direction)
    {
        $query = ContractTemplate::systemTemplates()
            ->whereHas('purchasedBy', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->withCount('contracts');

        $query = $this->applyFilters($query, $search, $category, $status);

        return $query->get();
    }

    /**
     * Get available system templates (not purchased)
     */
    private function getAvailableSystemTemplates($companyId, $search, $category, $status, $sort, $direction)
    {
        $query = ContractTemplate::systemTemplates()
            ->active()
            ->whereDoesntHave('purchasedBy', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->withCount('contracts');

        $query = $this->applyFilters($query, $search, $category, $status);

        return $query->get();
    }

    /**
     * Apply common filters to query
     */
    private function applyFilters($query, $search, $category, $status)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query;
    }

    /**
     * Sort templates collection
     */
    private function sortTemplates($templates, $sort, $direction)
    {
        $isAsc = $direction === 'asc';

        return $templates->sortBy(function ($template) use ($sort) {
            switch ($sort) {
                case 'name':
                    return $template->name;
                case 'category':
                    return $template->category ?? '';
                case 'contracts_count':
                    return $template->contracts_count ?? 0;
                case 'updated_at':
                    return $template->updated_at;
                case 'created_at':
                default:
                    return $template->created_at;
            }
        }, SORT_REGULAR, !$isAsc)->values();
    }

    /**
     * Get template statistics
     */
    private function getTemplateStats($companyTemplates, $purchasedTemplates, $availableSystemTemplates): array
    {
        $allTemplates = collect()
            ->concat($companyTemplates)
            ->concat($purchasedTemplates)
            ->concat($availableSystemTemplates);

        return [
            'total' => $allTemplates->count(),
            'company' => $companyTemplates->count(),
            'purchased' => $purchasedTemplates->count(),
            'available' => $availableSystemTemplates->count(),
            'active' => $allTemplates->where('is_active', true)->count(),
            'categories' => $allTemplates->pluck('category')->filter()->unique()->sort()->values()->toArray(),
        ];
    }

    /**
     * Get available categories for filters
     */
    public function getAvailableCategories(): array
    {
        $company = Auth::user()->currentCompany;

        $categories = collect();

        // Get categories from company templates
        $categories = $categories->concat(
            ContractTemplate::where('company_id', $company->id)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
        );

        // Get categories from purchased templates
        $categories = $categories->concat(
            ContractTemplate::systemTemplates()
                ->whereHas('purchasedBy', function ($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
        );

        // Get categories from available system templates
        $categories = $categories->concat(
            ContractTemplate::systemTemplates()
                ->active()
                ->whereDoesntHave('purchasedBy', function ($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
        );

        return $categories->filter()->unique()->sort()->values()->toArray();
    }
}

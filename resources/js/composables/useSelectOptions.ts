import { computed, type ComputedRef } from 'vue'

export interface SelectOption {
  value: string
  label: string
  disabled?: boolean
}

/**
 * Composable for handling select options with proper empty value handling
 * Ensures SelectItem components always have non-empty string values
 */
export function useSelectOptions() {
  /**
   * Creates select options with a default "all" option
   * @param options Array of options
   * @param allLabel Label for the "all" option (default: "All")
   * @param allValue Value for the "all" option (default: "all")
   */
  const createOptionsWithAll = (
    options: SelectOption[],
    allLabel = 'All',
    allValue = 'all'
  ): SelectOption[] => {
    return [
      { value: allValue, label: allLabel },
      ...options
    ]
  }

  /**
   * Status options for billboards
   */
  const billboardStatusOptions: ComputedRef<SelectOption[]> = computed(() =>
    createOptionsWithAll([
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'maintenance', label: 'Maintenance' },
    ], 'All Status')
  )

  /**
   * Contract status options
   */
  const contractStatusOptions: ComputedRef<SelectOption[]> = computed(() =>
    createOptionsWithAll([
      { value: 'draft', label: 'Draft' },
      { value: 'pending', label: 'Pending' },
      { value: 'active', label: 'Active' },
      { value: 'completed', label: 'Completed' },
      { value: 'cancelled', label: 'Cancelled' },
    ], 'All Status')
  )

  /**
   * Industry options
   */
  const industryOptions: ComputedRef<SelectOption[]> = computed(() => [
    { value: 'outdoor-advertising', label: 'Outdoor Advertising' },
    { value: 'marketing-agency', label: 'Marketing Agency' },
    { value: 'real-estate', label: 'Real Estate' },
    { value: 'retail', label: 'Retail' },
    { value: 'other', label: 'Other' },
  ])

  /**
   * Company size options
   */
  const companySizeOptions: ComputedRef<SelectOption[]> = computed(() => [
    { value: '1-10', label: '1-10 employees' },
    { value: '11-50', label: '11-50 employees' },
    { value: '51-200', label: '51-200 employees' },
    { value: '200+', label: '200+ employees' },
  ])

  /**
   * Billboard dimension options
   */
  const billboardDimensionOptions: ComputedRef<SelectOption[]> = computed(() => [
    { value: '14x48', label: '14m x 48m (Bulletin)' },
    { value: '12x24', label: '12m x 24m (Poster)' },
    { value: '6x12', label: '6m x 12m (Junior)' },
    { value: 'custom', label: 'Custom' },
  ])

  /**
   * User role options
   */
  const userRoleOptions: ComputedRef<SelectOption[]> = computed(() => [
    { value: 'manager', label: 'Manager' },
    { value: 'editor', label: 'Editor' },
    { value: 'viewer', label: 'Viewer' },
  ])

  /**
   * Converts a filter value to the appropriate backend value
   * @param value The frontend select value
   * @param allValue The value that represents "all" (default: "all")
   * @returns Empty string for "all", otherwise the original value
   */
  const toBackendValue = (value: string, allValue = 'all'): string => {
    return value === allValue ? '' : value
  }

  /**
   * Converts a backend value to the appropriate frontend value
   * @param value The backend value (empty string or actual value)
   * @param allValue The value that represents "all" (default: "all")
   * @returns "all" for empty string, otherwise the original value
   */
  const toFrontendValue = (value: string | undefined | null, allValue = 'all'): string => {
    return !value || value === '' ? allValue : value
  }

  return {
    createOptionsWithAll,
    billboardStatusOptions,
    contractStatusOptions,
    industryOptions,
    companySizeOptions,
    billboardDimensionOptions,
    userRoleOptions,
    toBackendValue,
    toFrontendValue,
  }
}

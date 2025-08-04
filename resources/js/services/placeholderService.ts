/**
 * Shared placeholder service for template and contract editors
 * Ensures consistency across all editors and provides a centralized place
 * to manage available placeholders
 */

export interface PlaceholderGroup {
  label: string
  placeholders: Placeholder[]
}

export interface Placeholder {
  value: string
  label: string
  description?: string
}

/**
 * All available placeholders organized by category
 */
export const placeholderGroups: PlaceholderGroup[] = [
  {
    label: 'Company Information',
    placeholders: [
      { value: '{{company_name}}', label: 'Company Name', description: 'Your company name' },
      { value: '{{company_logo}}', label: 'Company Logo', description: 'Full-size company logo' },
      { value: '{{company_logo_small}}', label: 'Company Logo (Small)', description: 'Small version of company logo' },
      { value: '{{company_address}}', label: 'Company Address', description: 'Complete company address' },
      { value: '{{company_city}}', label: 'Company City', description: 'Company city' },
      { value: '{{company_state}}', label: 'Company State', description: 'Company state/province' },
      { value: '{{company_zip_code}}', label: 'Company ZIP Code', description: 'Company postal code' },
      { value: '{{company_country}}', label: 'Company Country', description: 'Company country' },
      { value: '{{company_phone}}', label: 'Company Phone', description: 'Company phone number' },
      { value: '{{company_email}}', label: 'Company Email', description: 'Company email address' },
      { value: '{{company_website}}', label: 'Company Website', description: 'Company website URL' },
      { value: '{{company_tax_id}}', label: 'Company Tax ID', description: 'Company tax identification number' },
      { value: '{{company_description}}', label: 'Company Description', description: 'Company description' }
    ]
  },
  {
    label: 'Client Information',
    placeholders: [
      { value: '{{client_name}}', label: 'Client Name', description: 'Client full name' },
      { value: '{{client_company}}', label: 'Client Company', description: 'Client company name' },
      { value: '{{client_address}}', label: 'Client Address', description: 'Client complete address' },
      { value: '{{client_email}}', label: 'Client Email', description: 'Client email address' },
      { value: '{{client_phone}}', label: 'Client Phone', description: 'Client phone number' }
    ]
  },
  {
    label: 'Contract Details',
    placeholders: [
      { value: '{{contract_number}}', label: 'Contract Number', description: 'Unique contract identifier' },
      { value: '{{contract_type}}', label: 'Contract Type', description: 'Type of contract' },
      { value: '{{start_date}}', label: 'Start Date', description: 'Contract start date' },
      { value: '{{end_date}}', label: 'End Date', description: 'Contract end date' },
      { value: '{{status}}', label: 'Contract Status', description: 'Current contract status' },
      { value: '{{signed_at}}', label: 'Signed Date', description: 'Date contract was signed' },
      { value: '{{signed_by}}', label: 'Signed By', description: 'Who signed the contract' }
    ]
  },
  {
    label: 'Financial Information',
    placeholders: [
      { value: '{{total_amount}}', label: 'Total Amount', description: 'Total contract value' },
      { value: '{{monthly_amount}}', label: 'Monthly Amount', description: 'Monthly payment amount' },
      { value: '{{payment_terms}}', label: 'Payment Terms', description: 'Payment schedule and terms' },
      { value: '{{currency}}', label: 'Currency', description: 'Contract currency' },
      { value: '{{exchange_rate}}', label: 'Exchange Rate', description: 'Currency exchange rate' }
    ]
  },
  {
    label: 'Billboard Information',
    placeholders: [
      { value: '{{billboard_locations}}', label: 'Billboard Locations', description: 'List of billboard locations in contract' }
    ]
  },
  {
    label: 'Dates & System',
    placeholders: [
      { value: '{{today_date}}', label: 'Today\'s Date', description: 'Current date' },
      { value: '{{current_year}}', label: 'Current Year', description: 'Current year' },
      { value: '{{current_month}}', label: 'Current Month', description: 'Current month' }
    ]
  }
]

/**
 * Get all placeholders as a flat array
 */
export function getAllPlaceholders(): Placeholder[] {
  return placeholderGroups.flatMap(group => group.placeholders)
}

/**
 * Get placeholders by category
 */
export function getPlaceholdersByCategory(category: string): Placeholder[] {
  const group = placeholderGroups.find(g => g.label.toLowerCase().includes(category.toLowerCase()))
  return group?.placeholders || []
}

/**
 * Search placeholders by label or value
 */
export function searchPlaceholders(query: string): Placeholder[] {
  const lowercaseQuery = query.toLowerCase()
  return getAllPlaceholders().filter(placeholder =>
    placeholder.label.toLowerCase().includes(lowercaseQuery) ||
    placeholder.value.toLowerCase().includes(lowercaseQuery) ||
    placeholder.description?.toLowerCase().includes(lowercaseQuery)
  )
}

/**
 * Get placeholder by value
 */
export function getPlaceholderByValue(value: string): Placeholder | undefined {
  return getAllPlaceholders().find(p => p.value === value)
}

/**
 * Legacy array for backward compatibility
 */
export const templatePlaceholders = getAllPlaceholders()

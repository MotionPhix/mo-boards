import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    label: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface User {
  id: number
  name: string
  email: string
  phone?: string
  current_company_id?: number
  last_active_at?: string
  created_at: string
  updated_at: string
}

export interface Company {
  id: number
  uuid: string
  name: string
  slug: string
  industry?: string
  size?: '1-10' | '11-50' | '51-200' | '200+'
  address?: string
  subscription_plan: 'free' | 'pro' | 'business'
  subscription_expires_at?: string
  is_active: boolean
  created_at: string
  updated_at: string
  pivot?: {
    is_owner: boolean
    joined_at: string
  }
  billboards_count?: number
}

export interface Billboard {
  id: number
  uuid: string
  code: string
  name: string
  location: string
  coordinates: {
    latitude: string
    longitude: string
  }
  dimensions: {
    width: string
    height: string
    size: string
    area: number
  }
  pricing: {
    monthly_rate: string
    formatted_rate: string
    annual_rate: number
  }
  status: {
  current: 'active' | 'available' | 'maintenance' | 'removed'
    label: string
    color: string
    can_edit: boolean
  }
  description: string
  contracts: {
    active_count: number
    is_occupied: boolean
  }
  timestamps: {
    created_at: string
    updated_at: string
    created_at_human: string
    updated_at_human: string
  }
  actions: {
    can_view: boolean
    can_edit: boolean
    can_delete: boolean
    can_duplicate: boolean
  }
  company?: Company
  media?: MediaFile[]
}

export interface MediaFile {
  id: number
  model_type: string
  model_id: number
  uuid: string
  collection_name: string
  name: string
  file_name: string
  mime_type: string
  disk: string
  conversions_disk: string
  size: number
  manipulations: any[]
  custom_properties: any[]
  generated_conversions: any[]
  responsive_images: any[]
  order_column: number
  created_at: string
  updated_at: string
  original_url: string
  preview_url: string
}

export interface Contract {
  id: number
  uuid: string
  contract_number: string
  status: {
    current: string
    label: string
    color: string
  }
  client: {
    name: string
    email?: string
    phone?: string
    company?: string
    address?: string
  }
  dates: {
    start_date: string
    end_date: string
    signed_at?: string
    duration_months: number
  }
  financial: {
    currency: string
    currency_symbol: string
    exchange_rate: string
    total_amount: string
    monthly_amount: string
    formatted_total: string
    formatted_monthly: string
    company_currency: string
    currency_converted: boolean
  }
  terms: {
    payment_terms: string
    payment_terms_days: number
    late_fee_percentage: string
    tax_rate: string
  }
  billboards: Array<{
    id: number
    uuid: string
    code: string
    name: string
    location: string
    rate: number
    formatted_rate: string
    notes?: string
    dimensions?: string
  }>
  template?: {
    id: number
    uuid: string
    name: string
  }
  created_by: {
    id: number
    name: string
    email: string
  }
  company: {
    id: number
    name: string
    currency: string
    timezone: string
    date_format: string
  }
  notes?: string
  created_at: string
  updated_at: string
  actions: {
    can_view: boolean
    can_edit: boolean
    can_delete: boolean
    can_approve: boolean
    can_sign: boolean
    can_cancel: boolean
  }
}

export interface ContractTemplate {
  id: number
  uuid: string
  name: string
  description?: string
  content: string
  price?: string
  formatted_price?: string
  is_active: boolean
  is_premium: boolean
  is_system_template?: boolean
  category?: string
  tags?: string[]
  default_terms?: any
  custom_fields?: any
  features?: any
  preview_image?: string
  company?: {
    id: number
    uuid: string
    name: string
  }
  contracts_count?: number
  created_at: string
  updated_at: string
  actions: {
    can_view: boolean
    can_edit: boolean
    can_delete: boolean
    can_duplicate: boolean
    can_purchase: boolean
  }
}

export interface ContractTemplateWrapped {
  data: ContractTemplate
}

export interface TeamMember {
  id: number
  name: string
  email: string
  roles: Role[]
  permissions: Permission[]
  pivot?: {
    is_owner: boolean
    joined_at: string
  }
  last_active_at?: string
}

export interface Role {
  id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}

export interface Permission {
  id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}

export interface PageProps {
  user: User
  companies: Company[]
  currentCompany?: Company
  ziggy: any
  flash: {
    success?: string
    error?: string
    info?: string
  }
}

export type BreadcrumbItemType = BreadcrumbItem;

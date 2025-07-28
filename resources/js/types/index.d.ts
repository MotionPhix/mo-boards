import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
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
  name: string
  slug: string
  industry?: string
  size?: '1-10' | '11-50' | '51-200' | '200+'
  address?: string
  subscription_plan: 'starter' | 'professional' | 'enterprise'
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
  company_id: number
  name: string
  code: string
  location: string
  latitude?: number
  longitude?: number
  size?: string
  width?: number
  height?: number
  monthly_rate: number
  status: 'active' | 'inactive' | 'maintenance'
  description?: string
  created_at: string
  updated_at: string
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

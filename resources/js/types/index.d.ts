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

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Vendor {
    id: number;
    user_id: number;
    company_name: string;
    store_name: string;
    status: 'active' | 'inactive';
    created_at: string;
    updated_at: string;
    user: User;
}

export interface Category {
    id: number;
    name: string;
    description?: string;
    status: 'active' | 'inactive';
    parent_id?: number;
    created_at: string;
    updated_at: string;
}

export interface AttributeValue {
    id: number;
    attribute_id: number;
    value: string;
    hex?: string;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface Attribute {
    id: number;
    name: string;
    slug: string;
    description?: string;
    type: 'text' | 'color' | 'select' | 'boolean';
    is_required: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    values: AttributeValue[];
}

export interface Product {
    id: number;
    vendor_id: number;
    category_id: number;
    name: string;
    description?: string;
    base_price: number;
    sku?: string;
    status: 'active' | 'inactive';
    product_type: 'simple' | 'variable';
    stock_quantity?: number;
    created_at: string;
    updated_at: string;
    vendor: Vendor;
    category: Category;
    images: any[];
    product_attributes: any[];
    formatted_attributes?: any[];
    formatted_images?: any[];
}

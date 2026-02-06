export interface AuthUser {
  id: number;
  organization_id: number;
  name: string;
  email: string;
  role?: string;
  avatar?: string | null;
  has_2fa_enabled?: boolean;
  email_verified_at?: string | null;
  organization?: Organization | null;
}

export interface Organization {
  id: number;
  name: string;
  slug: string;
  currency_code: string;
  timezone: string;
  logo_path: string | null;
  subscription_tier: string;
  available_features: string[];
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
}

export interface AppPageProps {
  auth: {
    user: AuthUser | null;
  };
  tenant?: Organization | null;
  features?: string[];
  flash?: {
    success?: string | null;
    error?: string | null;
  };
  csrf_token?: string;
  appName?: string;
}

export type InvoiceStatus = 'draft' | 'sent' | 'viewed' | 'partial' | 'paid' | 'overdue' | 'cancelled';

export interface Invoice {
  id: number;
  organization_id: number;
  client_id: number;
  invoice_number: string;
  status: InvoiceStatus;
  issue_date: string;
  due_date: string;
  total: number;
  subtotal: number;
  tax_total: number;
  discount_total: number;
  currency_code: string;
  notes?: string;
  terms?: string;
  client?: Client;
  line_items?: InvoiceLineItem[];
  payments?: Payment[];
  created_at: string;
  updated_at: string;
}

export interface InvoiceLineItem {
  id: number;
  invoice_id: number;
  description: string;
  quantity: number;
  unit_price: number;
  total: number;
  tax_id?: number;
}

export interface Client {
  id: number;
  organization_id: number;
  name: string;
  email: string;
  company_name?: string;
  phone?: string;
  website?: string;
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  currency_code: string;
  balance: number;
  contacts?: Contact[];
  created_at: string;
}

export interface Contact {
  id: number;
  client_id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
}

export type ExpenseStatus = 'pending' | 'approved' | 'rejected' | 'reimbursed';

export interface Expense {
  id: number;
  organization_id: number;
  category_id: number;
  client_id?: number;
  amount: number;
  tax_amount: number;
  date: string;
  vendor: string;
  description?: string;
  status: ExpenseStatus;
  is_billable: boolean;
  receipt_path?: string;
  category?: ExpenseCategory;
  client?: Client;
  created_at: string;
}

export interface ExpenseCategory {
  id: number;
  organization_id: number;
  name: string;
  description?: string;
  color?: string;
}

export interface Payment {
  id: number;
  organization_id: number;
  invoice_id: number;
  amount: number;
  payment_date: string;
  payment_method: string;
  reference?: string;
  notes?: string;
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
}

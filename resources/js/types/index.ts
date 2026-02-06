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

import { defineStore } from 'pinia';
import { AuthUser, Organization } from '@/types';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as AuthUser | null,
    organization: null as Organization | null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    setUser(user: AuthUser | null) {
      this.user = user;
    },
    setOrganization(org: Organization | null) {
      this.organization = org;
    },
  },
});

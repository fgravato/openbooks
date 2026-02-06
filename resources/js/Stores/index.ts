import { defineStore } from 'pinia';

interface UiState {
  sidebarOpen: boolean;
}

export const useUiStore = defineStore('ui', {
  state: (): UiState => ({
    sidebarOpen: false,
  }),
  actions: {
    toggleSidebar(): void {
      this.sidebarOpen = !this.sidebarOpen;
    },
  },
});

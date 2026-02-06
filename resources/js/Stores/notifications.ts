import { defineStore } from 'pinia';

interface Notification {
  id: string;
  type: 'success' | 'error' | 'info' | 'warning';
  message: string;
  read: boolean;
}

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [] as Notification[],
  }),
  actions: {
    add(notification: Omit<Notification, 'id' | 'read'>) {
      this.notifications.push({
        ...notification,
        id: Math.random().toString(36).substr(2, 9),
        read: false,
      });
    },
    remove(id: string) {
      this.notifications = this.notifications.filter(n => n.id !== id);
    },
    markRead(id: string) {
      const notification = this.notifications.find(n => n.id === id);
      if (notification) {
        notification.read = true;
      }
    },
  },
});

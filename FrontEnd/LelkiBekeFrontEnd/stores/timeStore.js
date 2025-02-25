import { defineStore } from 'pinia';

export const useTimeStore = defineStore('time', {
  state: () => ({
    orderDates: new Map(), // Store order dates instead of elapsed times
  }),

  actions: {
    startTracking(orderId, startTime) {
      this.orderDates.set(orderId, startTime);
    },

    stopTracking(orderId) {
      this.orderDates.delete(orderId);
    },

    getElapsedTime(orderId) {
      const startTime = this.orderDates.get(orderId);
      if (!startTime) return 0;
      
      return Math.floor((new Date() - new Date(startTime)) / 60000);
    },

    clearAll() {
      this.orderDates.clear();
    }
  }
});

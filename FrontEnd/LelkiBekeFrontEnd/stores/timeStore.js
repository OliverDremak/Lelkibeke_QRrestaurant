import { defineStore } from 'pinia';

export const useTimeStore = defineStore('time', {
  state: () => ({
    timers: new Map(),
    intervals: new Map(),
  }),

  actions: {
    startTracking(orderId, startTime) {
      if (!this.timers.has(orderId)) {
        const elapsed = Math.floor((new Date() - new Date(startTime)) / 60000);
        this.timers.set(orderId, elapsed);
        
        const intervalId = setInterval(() => {
          const currentElapsed = Math.floor((new Date() - new Date(startTime)) / 60000);
          this.timers.set(orderId, currentElapsed);
        }, 60000);
        
        this.intervals.set(orderId, intervalId);
      }
    },

    stopTracking(orderId) {
      if (this.intervals.has(orderId)) {
        clearInterval(this.intervals.get(orderId));
        this.intervals.delete(orderId);
        this.timers.delete(orderId);
      }
    },

    getElapsedTime(orderId) {
      return this.timers.get(orderId) || 0;
    },

    clearAll() {
      this.intervals.forEach(intervalId => clearInterval(intervalId));
      this.intervals.clear();
      this.timers.clear();
    }
  }
});

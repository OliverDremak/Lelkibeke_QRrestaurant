import { defineStore } from 'pinia';
import { useTimeStore } from './timeStore';

export const useOrderStore = defineStore('orderStore', {
  state: () => ({
    orders: new Map(),
    orderPositions: new Map(), // Store original positions
    timeStore: null
  }),

  actions: {
    initialize() {
      if (!this.timeStore) {
        this.timeStore = useTimeStore();
      }
    },

    updateOrders(orderList) {
      this.initialize();
      const currentTime = Date.now();

      orderList.forEach(order => {
        const orderId = order.order_id;
        
        // Preserve or set initial position
        if (!this.orderPositions.has(orderId)) {
          this.orderPositions.set(orderId, {
            timestamp: new Date(order.order_date).getTime(),
            sortIndex: this.orderPositions.size
          });
        }

        // Update order data while maintaining position
        this.orders.set(orderId, {
          ...order,
          originalPosition: this.orderPositions.get(orderId)
        });
        
        this.timeStore.startTracking(orderId, order.order_date);
      });

      // Clean up removed orders
      [...this.orders.keys()].forEach(orderId => {
        if (!orderList.some(o => o.order_id === orderId)) {
          this.orders.delete(orderId);
          this.timeStore.stopTracking(orderId);
          // Don't remove from orderPositions to maintain stable sorting
        }
      });
    },

    getOrderPosition(orderId) {
      return this.orderPositions.get(orderId)?.sortIndex ?? 0;
    },

    getSortedOrders() {
      return Array.from(this.orders.values())
        .sort((a, b) => {
          // Sort by original creation timestamp
          return this.getOrderPosition(a.order_id) - this.getOrderPosition(b.order_id);
        });
    },

    getOrder(orderId) {
      return this.orders.get(orderId);
    },

    getElapsedTime(orderId) {
      this.initialize();
      return this.timeStore.getElapsedTime(orderId);
    },

    clearAll() {
      this.initialize();
      this.orders.clear();
      this.timeStore.clearAll();
    }
  }
});

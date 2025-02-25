import { defineStore } from 'pinia';
import { useTimeStore } from './timeStore';

export const useOrderStore = defineStore('orderStore', {
  state: () => ({
    orders: new Map(),
    orderPositions: new Map(), // Store original positions
    itemPositions: new Map(), // Add new map for item positions
    timeStore: null,
    originalPositions: new Map(), // Add new map for original positions
  }),

  actions: {
    initialize() {
      if (!this.timeStore) {
        this.timeStore = useTimeStore();
      }
    },

    updateOrders(orderList) {
      this.initialize();
      
      // Preserve existing positions and assign new ones to new orders
      orderList.forEach((order, index) => {
        const orderId = order.order_id;
        if (!this.originalPositions.has(orderId)) {
          this.originalPositions.set(orderId, index);
        }
      });

      // Update orders while maintaining original positions
      orderList.forEach(order => {
        const orderId = order.order_id;
        this.orders.set(orderId, {
          ...order,
          sortPosition: this.originalPositions.get(orderId)
        });
        this.timeStore.startTracking(orderId, order.order_date);
      });

      // Don't remove positions when cleaning up orders
      [...this.orders.keys()].forEach(orderId => {
        if (!orderList.some(o => o.order_id === orderId)) {
          this.orders.delete(orderId);
          this.timeStore.stopTracking(orderId);
        }
      });
    },

    getOrderPosition(orderId) {
      return this.originalPositions.get(orderId) ?? 999999;
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

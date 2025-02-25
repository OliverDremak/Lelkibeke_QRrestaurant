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
      
      // Process each order and update timer maps
      orderList.forEach((order, index) => {
        const orderId = order.order_id;
        
        // Handle position tracking
        if (!this.originalPositions.has(orderId)) {
          this.originalPositions.set(orderId, index);
        }

        // Always update time tracking to ensure it's correct
        this.timeStore.startTracking(orderId, order.order_date);

        // Update order data
        this.orders.set(orderId, {
          ...order,
          sortPosition: this.originalPositions.get(orderId)
        });
      });
      
      // Clean up removed orders
      [...this.orders.keys()].forEach(orderId => {
        if (!orderList.some(o => o.order_id === orderId)) {
          this.orders.delete(orderId);
          this.timeStore.stopTracking(orderId);
          this.originalPositions.delete(orderId);
        }
      });
    },

    addNewOrder(order) {
      this.initialize();
      const orderId = order.order_id;
      
      // Initialize timer immediately for new order
      this.timeStore.startTracking(orderId, order.order_date);
      
      // Set initial position if needed
      if (!this.originalPositions.has(orderId)) {
        this.originalPositions.set(orderId, this.originalPositions.size);
      }
      
      // Add order to store
      this.orders.set(orderId, {
        ...order,
        sortPosition: this.originalPositions.get(orderId)
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
    },

    // Add new method to update single order
    updateOrderStatus(orderId, newStatus) {
      const order = this.orders.get(orderId);
      if (order) {
        // Maintain the original order date when updating status
        this.orders.set(orderId, {
          ...order,
          status: newStatus,
          originalDate: order.order_date // Preserve original timestamp
        });
      }
    }
  }
});

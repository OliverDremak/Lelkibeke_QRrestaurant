<template>
  <div class="order-list mt-4">
    <div v-if="groupedOrders.length">
      <div v-for="order in groupedOrders" :key="order.order_id" class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3 d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="card-title mb-0">Order #{{ order.order_id }}</h5>
              <span class="badge" :class="statusBadgeClass(order.status)">
                {{ order.status }}
              </span>
            </div>
            <p class="card-text text-muted small mb-1">Order Date: {{ order.order_date }}</p>
            <p class="card-text text-muted small mb-2">Total Price: ${{ order.total_price }}</p>
            <p class="card-text small mb-1"><strong>Items:</strong></p>
            <ul class="list-unstyled small mb-2">
              <li v-for="item in order.items" :key="item.menu_item_name" class="mb-1">
                {{ item.menu_item_name }} (x{{ item.quantity }}) - <em v-if="item.notes">{{ item.notes }}</em>
              </li>
            </ul>
          </div>
          <button
            v-if="order.status !== 'done'"
            @click="confirmMarkAsServed(order)"
            class="btn btn-lg btn-success"
          >
            Mark as Served
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <p class="text-center">No orders for this table.</p>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmation" class="modal" tabindex="-1" role="dialog" style="display: block;">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Action</h5>
            <button type="button" class="close" @click="showConfirmation = false" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to mark this order as served?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showConfirmation = false">Cancel</button>
            <button type="button" class="btn btn-primary btn-lg" @click="markAsServed">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import axios from 'axios';

export default {
  props: {
    orders: {
      type: Array,
      required: true,
    },
  },
  setup(props) {
    const orders = ref(props.orders);
    const showConfirmation = ref(false);
    const selectedOrder = ref(null);

    const groupOrders = (orders) => {
      const grouped = orders.reduce((acc, order) => {
        const existingOrder = acc.find(o => o.order_id === order.order_id);
        if (existingOrder) {
          existingOrder.items.push({
            menu_item_name: order.menu_item_name,
            quantity: order.quantity,
            notes: order.notes,
          });
        } else {
          acc.push({
            order_id: order.order_id,
            order_date: order.order_date,
            status: order.status,
            total_price: order.total_price,
            items: [{
              menu_item_name: order.menu_item_name,
              quantity: order.quantity,
              notes: order.notes,
            }],
          });
        }
        return acc;
      }, []);
      return grouped;
    };

    const groupedOrders = ref(groupOrders(orders.value));

    watch(() => props.orders, (newOrders) => {
      orders.value = newOrders;
      groupedOrders.value = groupOrders(newOrders);
    });

    const confirmMarkAsServed = (order) => {
      selectedOrder.value = order;
      showConfirmation.value = true;
    };

    const markAsServed = async () => {
      if (selectedOrder.value) {
        try {
          await axios.post(`http://localhost:8000/api/setOrderStatus/${selectedOrder.value.order_id}/done`);
          const orderIndex = groupedOrders.value.findIndex(order => order.order_id === selectedOrder.value.order_id);
          if (orderIndex !== -1) {
            groupedOrders.value.splice(orderIndex, 1);
          }
          showConfirmation.value = false;
        } catch (error) {
          console.error('Error marking order as served:', error);
        }
      }
    };

    const statusBadgeClass = (status) => {
      return {
        'badge-warning': status === 'pending',
        'badge-info': status === 'preparing',
        'badge-success': status === 'done',
      };
    };

    return {
      groupedOrders,
      confirmMarkAsServed,
      markAsServed,
      statusBadgeClass,
      showConfirmation,
    };
  },
};
</script>

<style scoped>
.order-list {
  margin-top: 20px;
}

.card {
  border-radius: 8px;
}

.badge {
  font-size: 0.875em;
  padding: 0.375em 0.75em;
  border-radius: 12px;
}

.btn-success {
  font-size: 1.5em; /* Increase font size */
  padding: 0.75em 1.5em; /* Increase padding */
  color: black; /* Change text color to black */
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-dialog {
  max-width: 500px;
  margin: 1.75rem auto;
}

.modal-content {
  background: #fff;
  border-radius: 0.3rem;
  box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
  color: black; /* Change text color to black */
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-body {
  padding: 1rem;
  
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1rem;
  border-top: 1px solid #dee2e6;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #000;
  opacity: 0.5;
}

.close:hover {
  color: #000;
  opacity: 0.75;
}

.close:focus {
  outline: none;
  box-shadow: none;
}
</style>
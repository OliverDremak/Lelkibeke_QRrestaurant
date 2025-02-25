<template>
  <div class="table-grid">
    <div v-for="table in tables" :key="table.id" class="table-card-wrapper">
      <div
        :class="[
          'table-card',
          table.is_available === 1 ? 'available' : 'occupied',
          { 'scanned': scannedTableIds.includes(table.id) },
          { 'selected': selectedTableId === table.id }
        ]"
        @click="selectTable(table.id)"
      >
        <!-- Order notification bubble -->
        <div v-if="tableOrders[table.id]" class="order-bubble">
          <span v-if="tableOrders[table.id] > 1">{{ tableOrders[table.id] }}</span>
        </div>

        <div class="table-content">
          <div class="table-header">
            <h2 class="table-number">{{ table.table_number }}</h2>
            <div v-if="scannedTableIds.includes(table.id)" class="scan-indicator">
              {{ t('tableList.scanned') }}
            </div>
          </div>
          
          <div class="status-badge">
            {{ table.is_available === 1 ? t('tableList.available') : t('tableList.occupied') }}
          </div>
          
          <button
            class="toggle-button"
            :class="{ 'toggle-to-occupied': table.is_available === 1, 'toggle-to-available': !table.is_available }"
            @click.stop="toggleOccupancy(table)"
          >
            {{ table.is_available === 1 ? t('tableList.setoc') : t('tableList.setav') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useNuxtApp } from '#app';
import axios from 'axios';
import { useI18n } from '#imports'


export default {
  props: {
    tables: {
      type: Array,
      required: true,
    },
    selectedTableId: {
      type: Number,
      default: null
    }
  },
  setup(props, { emit }) {
    const { $ws } = useNuxtApp();
    const { t } = useI18n()
    const scannedTableIds = ref([]);
    const tableOrders = ref({});  // Tracks number of new orders per table

    const toggleOccupancy = async (table) => {
      console.log('Toggling occupancy status for table:', table);
      const newStatus = table.is_available === 1 ? false : true;
      try {
        await axios.post(`http://localhost:8000/api/setOccupancyStatus`, {
          id: table.id,
          is_available: newStatus
        });
        table.is_available = newStatus;
        emit('refresh-tables');
      } catch (error) {
        console.error('Error setting table occupancy status:', error);
      }
    };

    const addTableOrder = (tableId) => {
      if (!tableOrders.value[tableId]) {
        tableOrders.value[tableId] = 1;
      } else {
        tableOrders.value[tableId]++;
      }
    };

    const clearTableOrders = (tableId) => {
      tableOrders.value[tableId] = 0;
    };

    const selectTable = (tableId) => {
      clearTableOrders(tableId);
      emit('select-table', tableId);
    };

    const removeScannedTable = (tableId) => {
      const index = scannedTableIds.value.indexOf(tableId);
      if (index > -1) {
        scannedTableIds.value.splice(index, 1);
      }
    };

    onMounted(() => {
      if (import.meta.server) return;
      console.log('Listening for table events...');
      $ws.channel('tables')
        .listen('TableScanned', (e) => {
          console.log('Table scan event received:', e);
          // Convert tableId to number to ensure consistent comparison
          const receivedTableId = parseInt(e.tableId);
          const scannedTable = props.tables.find((table) => table.id === receivedTableId);
          console.log('Matched table:', scannedTable);
          
          if (scannedTable && scannedTable.is_available === 1) {
            // Add table to scanned tables if not already present
            if (!scannedTableIds.value.includes(receivedTableId)) {
              scannedTableIds.value.push(receivedTableId);
              
              // Remove this specific table from scanned tables after 1 minute
              setTimeout(() => {
                removeScannedTable(receivedTableId);
              }, 60000);
            }
          }
        });
      $ws.channel('orders')
        .listen('OrderSent', (e) => {
          console.log('Order received for table ID:', e.tableId);
          addTableOrder(e.tableId);
          // Fix: Access selectedTableId through props
          if (props.selectedTableId === e.tableId) {
            selectTable(e.tableId);
          }
        })
        .listen('OrderStatusChanged', (e) => {
          console.log('Order status changed:', e);
          // Add notifications for new orders or when orders are ready
          if (e.isNewOrder || e.status === 'cooked') {
            addTableOrder(e.tableId);
          }
          // Refresh if this is the selected table
          if (props.selectedTableId === e.tableId) {
            selectTable(e.tableId);
          }
        });
    });

    onBeforeUnmount(() => {
      $ws.leaveChannel('tables');
    });

    return {
      scannedTableIds,
      toggleOccupancy,
      tableOrders,
      selectTable,
    };
  },
};
</script>

<style scoped>
.table-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 1rem;
}

.table-card {
  min-height: 200px;
  border-radius: 16px;
  padding: 1.5rem;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  transform-origin: center;
  overflow: visible; /* Allow notifications to overflow */
}

.table-card.available {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
}

.table-card.occupied {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.table-card.scanned::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 4px solid #f1c40f;
  border-radius: 16px;
  animation: pulse 2s infinite;
  pointer-events: none;
}

/* Selected table styling */
.table-card.selected {
  transform: scale(1.05);
  z-index: 2;
  box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.5),
              0 8px 20px rgba(0, 0, 0, 0.2);
}

.table-card.selected::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 18px;
  background: linear-gradient(45deg, #ffffff, transparent);
  opacity: 0.6;
  z-index: -1;
}

.table-card.selected .table-number {
  transform: scale(1.1);
}

.table-card.selected .status-badge {
  background: rgba(255, 255, 255, 0.25);
  font-weight: 700;
}

/* Add a crown icon or marker for selected table */
.table-card.selected .table-header::before {
  content: '★';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 1.5rem;
  color: #fff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  animation: float 2s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(-5px); }
}

.table-content {
  position: relative;
  z-index: 1;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.table-number {
  font-size: 2.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.scan-indicator {
  background: #f1c40f;
  color: #000;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.9rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-badge {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(5px);
  color: white;
  font-size: 1.2rem;
  font-weight: 600;
  text-align: center;
  margin: 1rem 0;
  padding: 0.75rem;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-text {
  text-align: center;
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
  margin: 1rem 0;
  padding: 0.5rem;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.1);
}

.status-value {
  font-weight: bold;
  font-size: 1.2rem;
  display: block;
  margin-top: 0.3rem;
}

.toggle-button {
  margin-top: auto;
  width: 100%;
  padding: 1.2rem;
  border: none;
  border-radius: 12px;
  font-size: 1.2rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.toggle-to-occupied {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
}

.toggle-to-available {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
}

.toggle-button:active {
  transform: translateY(2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(241, 196, 15, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(241, 196, 15, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(241, 196, 15, 0);
  }
}

.order-bubble {
  position: absolute;
  top: -12px;
  right: -12px;
  background: #e74c3c; /* Red color for cooked orders notification */
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: bold;
  box-shadow: 0 3px 6px rgba(0,0,0,0.3);
  border: 3px solid white;
  z-index: 10;
  transform-origin: center;
  animation: bubble-pop 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes bubble-pop {
  0% {
    transform: scale(0);
  }
  80% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

@media (max-width: 768px) {
  .table-grid {
    grid-template-columns: 1fr;
  }

  .table-card {
    min-height: 180px;
  }

  .toggle-button {
    padding: 1.5rem;
    font-size: 1.3rem;
  }

  .status-text {
    font-size: 1.2rem;
    margin: 1.5rem 0;
  }
}
</style>
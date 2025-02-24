<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div v-if="show" class="modal-backdrop" @click="onBackdropClick">
        <div class="modal-container" role="dialog" aria-modal="true" @click.stop>
          <header class="modal-header">
            <h3>{{ title }}</h3>
            <button class="close-button" @click="onCancel" aria-label="Close">×</button>
          </header>
          
          <div class="modal-content">
            <p>{{ message }}</p>
            <div class="order-details" v-if="orderDetails">
              <p class="detail-item">Order #{{ orderDetails.order_id }}</p>
              <p class="detail-item">Table {{ orderDetails.table_id }}</p>
              <p class="detail-item">Status: {{ orderDetails.status }}</p>
            </div>
          </div>

          <footer class="modal-actions">
            <button 
              class="cancel-btn" 
              @click="onCancel"
              ref="cancelBtn"
            >
              Cancel
            </button>
            <button 
              class="confirm-btn" 
              @click="onConfirm"
              :disabled="isProcessing"
              ref="confirmBtn"
            >
              {{ isProcessing ? 'Processing...' : 'Confirm' }}
            </button>
          </footer>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure?'
  },
  orderDetails: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['confirm', 'cancel', 'close']);
const isProcessing = ref(false);
const confirmBtn = ref(null);
const cancelBtn = ref(null);

const onConfirm = async () => {
  isProcessing.value = true;
  try {
    await emit('confirm');
  } finally {
    isProcessing.value = false;
  }
};

const onCancel = () => {
  emit('cancel');
  emit('close');
};

const onBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    onCancel();
  }
};

const handleKeydown = (event) => {
  if (!props.show) return;
  
  switch (event.key) {
    case 'Escape':
      onCancel();
      break;
    case 'Tab':
      if (!event.shiftKey && document.activeElement === confirmBtn.value) {
        event.preventDefault();
        cancelBtn.value?.focus();
      }
      if (event.shiftKey && document.activeElement === cancelBtn.value) {
        event.preventDefault();
        confirmBtn.value?.focus();
      }
      break;
  }
};

watch(() => props.show, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden';
    confirmBtn.value?.focus();
  } else {
    document.body.style.overflow = '';
  }
});

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown);
  document.body.style.overflow = '';
});
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-container {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  animation: modal-in 0.3s ease-out;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  color: #2c3e50;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  color: #666;
  transition: color 0.2s;
}

.close-button:hover {
  color: #e74c3c;
}

.modal-content {
  padding: 1.5rem;
}

.order-details {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.detail-item {
  margin: 0.5rem 0;
  color: #666;
}

.modal-actions {
  padding: 1.5rem;
  border-top: 1px solid #eee;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.cancel-btn, .confirm-btn {
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-btn {
  background: #e9ecef;
  border: none;
  color: #495057;
}

.confirm-btn {
  background: #2ecc71;
  border: none;
  color: white;
}

.confirm-btn:disabled {
  background: #95a5a6;
  cursor: not-allowed;
}

.cancel-btn:hover {
  background: #dee2e6;
}

.confirm-btn:hover:not(:disabled) {
  background: #27ae60;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@keyframes modal-in {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .modal-container {
    width: 95%;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .cancel-btn, .confirm-btn {
    width: 100%;
    padding: 1rem;
  }
}
</style>

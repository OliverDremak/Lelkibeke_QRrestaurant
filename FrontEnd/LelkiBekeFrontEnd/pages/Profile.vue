<script setup lang="ts">
import { ref, onMounted, watchEffect } from 'vue';
import axios from 'axios';
import { useAuthStore } from '~/stores/auth';
import { useRouter } from 'vue-router';
import Navbar from '~/components/Navbar.vue';

const notification = ref({ show: false, message: '', type: 'success' });
const auth = useAuthStore();
const router = useRouter();
const coupons = ref([]);
const orders = ref([]);
const isEditing = ref(false);
const userForm = ref({
    name: auth.user?.name,
    email: auth.user?.email,
    password: '',
    newPassword: ''
});

watchEffect(() => {
  if (!auth.token) {
    router.push('/auth');
  }
});

const fetchCoupons = async () => {
    try {
        const response = await axios.get(`http://localhost:8000/api/coupons/user/${auth.user.id}`);
        coupons.value = response.data;
    } catch (error) {
        console.error('Error fetching coupons:', error);
    }
};

const fetchOrders = async () => {
    try {
        const response = await axios.get(`http://localhost:8000/api/orders/user/${auth.user.id}`);
        orders.value = response.data;
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
};

const updateProfile = async () => {
    try {
        await axios.put(`http://localhost:8000/api/users/${auth.user.id}`, userForm.value);
        auth.user.name = userForm.value.name;
        auth.user.email = userForm.value.email;
        isEditing.value = false;
        
        // Show success notification
        notification.value = {
            show: true,
            message: 'Profile updated successfully!',
            type: 'success'
        };
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    } catch (error: any) {
        // Show error notification
        notification.value = {
            show: true,
            message: error.response?.data?.error || 'Failed to update profile',
            type: 'error'
        };
        
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    }
};

onMounted(() => {
    if (!auth.token) {
        router.push('/auth');
        return;
    }
    fetchCoupons();
    fetchOrders();
});
</script>

<template>
  <div>
    <Navbar />
    
    <!-- Add notification component -->
    <div v-if="notification.show" 
         :class="['notification', notification.type]"
         role="alert">
      {{ notification.message }}
    </div>
    
    <div class="profile-page">
      <div class="profile-container">
        <div class="profile-header">
          <h1>Profile</h1>
          <div class="user-info">
            <div class="avatar">{{ auth.user?.name[0].toUpperCase() }}</div>
            <div class="user-details">
              <h2>{{ auth.user?.name }}</h2>
              <p>{{ auth.user?.email }}</p>
            </div>
          </div>
        </div>

        <div class="settings-section">
          <h2>Account Settings</h2>
          <div v-if="!isEditing" class="settings-view">
            <button @click="isEditing = true" class="edit-button">Edit Profile</button>
          </div>
          <form v-else @submit.prevent="updateProfile" class="settings-form">
            <div class="form-group">
              <label>Name</label>
              <input v-model="userForm.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="userForm.email" type="email" required>
            </div>
            <div class="form-group">
              <label>Current Password</label>
              <input v-model="userForm.password" type="password">
            </div>
            <div class="form-group">
              <label>New Password (optional)</label>
              <input v-model="userForm.newPassword" type="password">
            </div>
            <div class="form-actions">
              <button type="submit" class="save-button">Save Changes</button>
              <button type="button" @click="isEditing = false" class="cancel-button">Cancel</button>
            </div>
          </form>
        </div>

        <div class="orders-section">
          <h2>Order History</h2>
          <div v-if="orders.length === 0" class="no-orders">
            <p>You haven't placed any orders yet.</p>
          </div>
          <div v-else class="orders-list">
            <div v-for="order in orders" :key="order.id" class="order-card">
              <div class="order-header">
                <span class="order-id">Order #{{ order.id }}</span>
                <span class="order-date">{{ new Date(order.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="order-items">
                <div v-for="item in order.items" :key="item.id" class="order-item">
                  <span>{{ item.quantity }}x {{ item.name }}</span>
                  <span>${{ item.price }}</span>
                </div>
              </div>
              <div class="order-total">
                <span>Total:</span>
                <span>${{ order.total }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="coupons-section">
          <h2>Your Coupons</h2>
          <div v-if="coupons.length === 0" class="no-coupons">
            <p>You have no coupons yet. Place 10 orders to get a discount coupon!</p>
          </div>
          <div v-else class="coupons-grid">
            <div v-for="coupon in coupons" :key="coupon.id" class="coupon-card">
              <div class="coupon-content">
                <div class="discount">{{ coupon.discount }}% OFF</div>
                <div class="code">{{ coupon.code }}</div>
                <div class="expires">Expires: {{ new Date(coupon.expires_at).toLocaleDateString() }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 2rem;
}

.profile-container {
  max-width: 800px;
  margin: 0 auto;
  background-color: white;
  border-radius: 15px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.profile-header {
  margin-bottom: 2rem;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 1rem;
}

.profile-header h1 {
  color: #333;
  margin-bottom: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.avatar {
  width: 60px;
  height: 60px;
  background: linear-gradient(145deg, #dd6013, #ffbd00);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
}

.user-details h2 {
  margin: 0;
  color: #333;
  font-size: 1.25rem;
}

.user-details p {
  margin: 0.5rem 0 0;
  color: #666;
}

.coupons-section {
  margin-top: 2rem;
}

.coupons-section h2 {
  color: #333;
  margin-bottom: 1.5rem;
}

.no-coupons {
  text-align: center;
  padding: 2rem;
  background-color: #f9f9f9;
  border-radius: 10px;
  color: #666;
}

.coupons-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.coupon-card {
  background: linear-gradient(145deg, #ffffff, #f0f0f0);
  border-radius: 10px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.coupon-card:hover {
  transform: translateY(-5px);
}

.coupon-content {
  text-align: center;
}

.discount {
  font-size: 1.5rem;
  font-weight: bold;
  color: #dd6013;
  margin-bottom: 0.5rem;
}

.code {
  background-color: #f5f5f5;
  padding: 0.5rem;
  border-radius: 5px;
  font-family: monospace;
  margin: 0.5rem 0;
}

.expires {
  font-size: 0.9rem;
  color: #666;
}

.settings-section {
  margin-top: 2rem;
  padding: 1.5rem;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #333;
}

.form-group input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.edit-button, .save-button, .cancel-button {
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
}

.save-button {
  background-color: #dd6013;
  color: white;
  border: none;
}

.cancel-button {
  background-color: #f5f5f5;
  border: 1px solid #ddd;
}

.orders-section {
  margin-top: 2rem;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.order-card {
  background-color: white;
  border-radius: 10px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #eee;
}

.order-id {
  font-weight: 600;
  color: #dd6013;
}

.order-date {
  color: #666;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  color: #333;
}

.order-total {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
  padding-top: 0.5rem;
  border-top: 1px solid #eee;
  font-weight: 600;
}

.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem 2rem;
  border-radius: 8px;
  z-index: 1000;
  animation: slideIn 0.3s ease-out;
}

.success {
  background-color: #4caf50;
  color: white;
}

.error {
  background-color: #f44336;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .profile-page {
    padding: 1rem;
  }

  .profile-container {
    padding: 1rem;
  }

  .coupons-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }
  
  .order-card {
    padding: 1rem;
  }

  .notification {
    width: 90%;
    top: 10px;
    right: 5%;
    text-align: center;
  }
}
</style>

<script setup lang="ts">
import { ref, onMounted, watchEffect } from 'vue';
import axios from 'axios';
import { useAuthStore } from '~/stores/auth';
import { useRouter } from 'vue-router';
import Navbar from '~/components/Navbar.vue';

const auth = useAuthStore();
const router = useRouter();
const coupons = ref([]);

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

onMounted(() => {
    if (!auth.token) {
        router.push('/auth');
        return;
    }
    fetchCoupons();
});
</script>

<template>
  <div>
    <Navbar />
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
}
</style>

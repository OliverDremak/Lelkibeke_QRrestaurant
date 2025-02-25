<script setup>
import { useRouter } from 'vue-router';

definePageMeta({
  requiresAuth: false
});

const router = useRouter();

const goBack = () => {
  router.go(-1); // Go back to previous page
};

const returnToMain = () => {
  router.push('/');
};
</script>

<template>
  <div class="thank-you-container">
    <div class="thank-you-card">
      <div class="success-icon">✓</div>
      <h1>Thank You!</h1>
      <p class="message">Your order has been successfully placed</p>
      
      <div class="divider"></div>
      
      <div class="buttons">
        <button class="order-again-btn" @click="goBack">
          Order Again
        </button>
        <button class="return-btn" @click="returnToMain">
          Return to Main
        </button>
      </div>

      <div class="social-links">
        <a href="https://facebook.com/name" target="_blank" class="social-btn">
          <span class="icon">📱</span>
          Follow Us
        </a>
        <a href="https://reviewsite.com/url" target="_blank" class="social-btn">
          <span class="icon">⭐</span>
          Rate Us
        </a>
      </div>
    </div>
  </div>
</template>

<style scoped>
.thank-you-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
  padding: 1rem;
}

.thank-you-card {
  background: white;
  border-radius: 24px;
  padding: 2.5rem;
  text-align: center;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 90%;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #4CAF50;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: white;
  font-size: 40px;
  animation: pop 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

h1 {
  color: #2E7D32;
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.message {
  color: #666;
  font-size: 1.2rem;
  margin-bottom: 2rem;
}

.divider {
  height: 2px;
  background: #E8F5E9;
  margin: 2rem 0;
}

.buttons {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.order-again-btn, .return-btn {
  flex: 1;
  padding: 1rem;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.order-again-btn {
  background: #4CAF50;
  color: white;
}

.return-btn {
  background: #E8F5E9;
  color: #2E7D32;
}

.order-again-btn:hover, .return-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.social-links {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.social-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.2rem;
  border-radius: 10px;
  text-decoration: none;
  color: #666;
  background: #f5f5f5;
  transition: all 0.2s;
}

.social-btn:hover {
  background: #E8F5E9;
  color: #2E7D32;
}

.icon {
  font-size: 1.2rem;
}

@keyframes pop {
  0% { transform: scale(0); }
  70% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

@media (max-width: 600px) {
  .buttons {
    flex-direction: column;
  }
  
  .social-links {
    flex-direction: column;
  }
  
  .thank-you-card {
    padding: 2rem;
  }
}
</style>


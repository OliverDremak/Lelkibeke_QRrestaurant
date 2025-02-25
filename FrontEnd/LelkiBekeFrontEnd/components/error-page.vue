<template>
  <div class="error-container">
    <div class="error-card">
      <div class="error-icon">
        ⚠️
      </div>
      <h1 class="error-title">{{ t('errorPage.title') }}</h1>
      <p class="error-message">{{ errorMessage }}</p>
      <button @click="goToHome" class="return-button">
        {{ t('errorPage.goHome') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

import { useI18n } from '#imports'
const { t } = useI18n()

const router = useRouter();

defineProps({
  errorMessage: {
    type: String,
    default: 'An error occurred'
  }
});

const goToHome = () => {
  router.push('/');
};
</script>

<style scoped>
.error-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f8f9fa;
  padding: 20px;
}

.error-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.error-icon {
  font-size: 48px;
  margin-bottom: 20px;
}

.error-title {
  color: #dc3545;
  margin-bottom: 16px;
  font-size: 24px;
}

.error-message {
  color: #6c757d;
  margin-bottom: 24px;
  font-size: 16px;
}

.return-button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: 16px;
}

.return-button:hover {
  background-color: #0056b3;
}

@media (max-width: 480px) {
  .error-card {
    padding: 20px;
  }
  
  .error-title {
    font-size: 20px;
  }
  
  .error-message {
    font-size: 14px;
  }
  
  .return-button {
    padding: 10px 20px;
    font-size: 14px;
  }
}
</style>

<script setup lang="ts">
import axios from 'axios';

const route = useRoute();
const token = route.query.token as string;
const email = route.query.email as string;
const password = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isLoading = ref(false);
const resetSuccess = ref(false);
const resetError = ref('');

// Validate URL parameters
onMounted(() => {
  if (!token || !email) {
    navigateTo('/auth');
  }
});

const resetPassword = async () => {
  // Validate passwords
  if (password.value !== confirmPassword.value) {
    resetError.value = 'Passwords do not match';
    return;
  }
  
  if (password.value.length < 8) {
    resetError.value = 'Password must be at least 8 characters';
    return;
  }
  
  resetError.value = '';
  isLoading.value = true;
  
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/reset-password', {
      email: email,
      token: token,
      password: password.value
    });
    
    resetSuccess.value = true;
  } catch (error) {
    resetError.value = error.response?.data?.error || 'Failed to reset password';
  } finally {
    isLoading.value = false;
  }
};

import PasswordEyeIcon from '~/components/PasswordEyeIcon.vue';
</script>

<template>
  <div class="reset-page">
    <div class="reset-container fade-in-bottom">
      <template v-if="!resetSuccess">
        <h1 class="text-center display-3 heading">Reset Password</h1>
        
        <form @submit.prevent="resetPassword">
          <div class="form-floating mb-3">
            <input 
              v-model="password" 
              :type="showPassword ? 'text' : 'password'" 
              class="form-control" 
              id="password" 
              placeholder=""
              required
            >
            <label for="password">New Password</label>
            <button 
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
              aria-label="Toggle password visibility"
            >
              <PasswordEyeIcon :visible="showPassword" />
            </button>
          </div>
          
          <div class="form-floating mb-3">
            <input 
              v-model="confirmPassword" 
              :type="showConfirmPassword ? 'text' : 'password'" 
              class="form-control" 
              id="confirmPassword" 
              placeholder=""
              required
            >
            <label for="confirmPassword">Confirm Password</label>
            <button 
              type="button"
              class="password-toggle"
              @click="showConfirmPassword = !showConfirmPassword"
              aria-label="Toggle password visibility"
            >
              <PasswordEyeIcon :visible="showConfirmPassword" />
            </button>
          </div>
          
          <div class="error-message" v-if="resetError">{{ resetError }}</div>
          
          <button type="submit" :disabled="isLoading" class="glow-button">
            {{ isLoading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </form>
      </template>
      
      <template v-else>
        <div class="reset-success">
          <div class="success-icon">✓</div>
          <h2>Password Reset Successful</h2>
          <p>Your password has been reset successfully. You can now log in with your new password.</p>
          <button @click="navigateTo('/auth')" class="glow-button mt-4">
            Go to Login
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
@import url("~/assets/css/main.css");

.reset-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f5f5 0%, #e5e5e5 100%);
  padding: 2rem 1rem;
  transition: background 0.3s ease;
}

:root.dark .reset-page {
  background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
}

.reset-container {
  max-width: 400px;
  width: 100%;
  margin: auto;
  padding: 2.5rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
  border-radius: 20px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

:root.dark .reset-container {
  background-color: #2d2d2d;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  color: #e0e0e0;
}

.heading {
  color: #dd6013;
  margin-bottom: 2rem;
  font-size: 2.5rem;
  text-align: center;
  font-weight: bold;
}

:root.dark .heading {
  color: #ffbd00;
}

.form-floating {
  margin-bottom: 1.5rem;
  position: relative;
}

.form-control {
  border: 2px solid #ddd;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #dd6013;
  box-shadow: 0 0 0 0.2rem rgba(221, 96, 19, 0.25);
}

.form-floating input {
  padding-right: 45px !important;
}

.password-toggle {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 5px;
  cursor: pointer;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  z-index: 3;
}

.password-toggle:hover {
  color: #9E7E5E;
}

.glow-button {
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
  padding: 1rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 1rem;
  width: 100%;
}

.glow-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(221, 96, 19, 0.4);
}

.glow-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  text-align: center;
  font-weight: 500;
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

.reset-success {
  text-align: center;
  padding: 1rem 0;
}

.reset-success h2 {
  color: #4CAF50;
  margin-bottom: 1rem;
}

.reset-success p {
  color: #666;
  margin-bottom: 1.5rem;
}

@keyframes pop {
  0% { transform: scale(0); }
  70% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.mt-4 {
  margin-top: 1rem;
}

:root.dark .glow-button {
  background: linear-gradient(45deg, #ffbd00, #dd6013);
}

:root.dark .error-message {
  color: #f87171;
}

:root.dark .reset-success p {
  color: #e0e0e0;
}

:root.dark .reset-success h2 {
  color: #6ECF70;
}
</style>

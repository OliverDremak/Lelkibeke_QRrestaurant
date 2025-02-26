<script setup lang="ts">
  import axios from 'axios';

  const route = useRoute()
  const isLogin = ref(!route.query.register)
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const auth = useAuthStore()
  const showPassword = ref(false);
  const isForgotPassword = ref(false);
  const forgotEmail = ref('');
  const resetSuccess = ref(false);
  const resetError = ref('');
  const isLoading = ref(false);
  
  // Get redirect path from query parameters or use default
  const redirectPath = computed(() => {
    return (route.query.redirect as string) || '/'
  })
  
  const handleLogin = async () => {
    await auth.login(email.value, password.value)

    if (auth.user) {
      // Retrieve the intended URL from localStorage
      const intendedUrl = localStorage.getItem('intendedUrl');
      if (intendedUrl) {
        localStorage.removeItem('intendedUrl');
        await navigateTo(intendedUrl);
      } else {
        await navigateTo('/');
      }
    }
  }

  const submitForm = async () => {
    if (isLogin.value) {
      await handleLogin()
    } else {
      await auth.register(name.value, email.value, password.value)
    }
    
    if (auth.user) {
      navigateTo(redirectPath.value)
    }
  }

  const submitForgotPassword = async () => {
    resetSuccess.value = false;
    resetError.value = '';
    isLoading.value = true;
    
    try {
      const response = await axios.post('http://localhost:8000/api/forgot-password', {
        email: forgotEmail.value
      });
      
      // Axios automatically parses JSON responses
      resetSuccess.value = true;
    } catch (error) {
      resetError.value = error.response?.data?.error || 'Failed to send reset link';
    } finally {
      isLoading.value = false;
    }
  }
  
  const backToLogin = () => {
    isForgotPassword.value = false;
    resetSuccess.value = false;
    resetError.value = '';
  }

  import PasswordEyeIcon from '~/components/PasswordEyeIcon.vue'
</script>

<template>
  <div class="auth-page">
    <div class="auth-container fade-in-bottom">
      <template v-if="!isForgotPassword">
        <h1 class="text-center display-3 heading">{{ isLogin ? 'Login' : 'Register' }}</h1>

        <form @submit.prevent="submitForm">
          <div v-if="!isLogin" class="form-floating mb-3">
            <input v-model="name" type="text" class="form-control" id="floatingInput0" required placeholder="">
            <label for="floatingInput0">Name</label>
          </div>

          <div class="form-floating mb-3">
              <input v-model="email" type="email" class="form-control" id="floatingInput1" placeholder="">
              <label for="floatingInput1">Email address</label>
          </div>

          <div class="form-floating mb-3">
            <input 
              v-model="password" 
              :type="showPassword ? 'text' : 'password'" 
              class="form-control" 
              id="floatingInput2" 
              placeholder=""
            >
            <label for="floatingInput2">Password</label>
            <button 
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
              aria-label="Toggle password visibility"
            >
              <PasswordEyeIcon :visible="showPassword" />
            </button>
          </div>

          <div class="error-message" v-if="auth.error">{{ auth.error }}</div>

          <button type="submit" :disabled="auth.loading" class="glow-button">
            {{ auth.loading ? 'Processing...' : isLogin ? 'Login' : 'Register' }}
          </button>
        </form>

        <button @click="isLogin = !isLogin" class="toggle-button">
          <span class="text-center w-100 d-block">{{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}</span>
        </button>
        
        <button v-if="isLogin" @click="isForgotPassword = true" class="forgot-button">
          <span class="text-center w-100 d-block">Forgot Password?</span>
        </button>
        
        <button @click="navigateTo('/')" class="home-button">
          <span class="button-text">Back to Home</span>
        </button>
      </template>
      
      <template v-else>
        <h1 class="text-center display-3 heading">Reset Password</h1>
        
        <div v-if="!resetSuccess" class="forgot-password-form">
          <p class="text-center mb-4">Enter your email address and we'll send you a link to reset your password.</p>
          
          <form @submit.prevent="submitForgotPassword">
            <div class="form-floating mb-3">
              <input v-model="forgotEmail" type="email" class="form-control" id="forgotEmail" required placeholder="">
              <label for="forgotEmail">Email address</label>
            </div>
            
            <div class="error-message" v-if="resetError">{{ resetError }}</div>
            
            <button type="submit" :disabled="isLoading" class="glow-button">
              {{ isLoading ? 'Sending...' : 'Send Reset Link' }}
            </button>
          </form>
        </div>
        
        <div v-else class="reset-success">
          <div class="success-icon">✓</div>
          <h2>Email Sent</h2>
          <p>We've sent a password reset link to your email address. Please check your inbox and follow the instructions.</p>
        </div>
        
        <button @click="backToLogin" class="toggle-button mt-4">
          <span class="text-center w-100 d-block">Back to Login</span>
        </button>
      </template>
    </div>
  </div>
</template>

<style scoped>
@import url("~/assets/css/main.css");

.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f5f5 0%, #e5e5e5 100%);
  padding: 2rem 1rem;
  transition: background 0.3s ease;
}

:root.dark .auth-page {
  background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
}

.auth-container {
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

:root.dark .auth-container {
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

.toggle-button {
  width: 100%;
  text-align: center;
  background: none;
  border: none;
  color: #dd6013;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1rem;
  text-decoration: none;
  padding: 0.5rem;
}

.toggle-button:hover {
  color: #ffbd00;
}

.back-button {
  margin-top: 1rem;
  background: #6c757d;
  color: white;
  border-radius: 5px;
}

.back-button:hover {
  background: #5a6268;
}

.form-floating input,
.form-floating textarea {
  transition: all 0.3s ease;
}

:root.dark .form-floating input,
:root.dark .form-floating textarea {
  background-color: #2d2d2d;
  color: #e0e0e0;
  border-color: #404040;
}

:root.dark .form-floating label {
  color: #808080;
}

:root.dark .toggle-button {
  color: #e0e0e0;
}

:root.dark .toggle-button:hover {
  color: #dd6013;
}

:root.dark .back-button {
  background: #404040;
  color: #e0e0e0;
}

:root.dark .back-button:hover {
  background: #505050;
}

@media (max-width: 480px) {
  .auth-container {
    padding: 1.5rem;
  }
  
  .heading {
    font-size: 2rem;
  }
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  text-align: center;
  font-weight: 500;
}

.password-input {
  position: relative;
  width: 100%;
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
  width: auto;
  z-index: 2;
}

.password-toggle:hover {
  color: #9E7E5E;
}

.password-toggle .material-symbols-outlined {
  font-size: 20px;
}

.form-floating .password-input input {
  padding-right: 40px;
}

.form-floating {
  position: relative;
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

.form-floating input {
  padding-right: 45px !important;
}

.home-button {
  width: 100%;
  margin-top: 1rem;
  padding: 0.75rem;
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(221, 96, 19, 0.2);
}

.home-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(221, 96, 19, 0.3);
}

.home-icon {
  font-size: 1.2rem;
}

.button-text {
  font-size: 1.1rem;
}

:root.dark .home-button {
  background: linear-gradient(45deg, #ffbd00, #dd6013);
  color: white;
  box-shadow: 0 4px 15px rgba(255, 189, 0, 0.2);
}

:root.dark .home-button:hover {
  box-shadow: 0 6px 20px rgba(255, 189, 0, 0.3);
}

.forgot-button {
  width: 100%;
  text-align: center;
  background: none;
  border: none;
  color: #dd6013;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.5rem;
  text-decoration: none;
  padding: 0.5rem;
  opacity: 0.8;
}

.forgot-button:hover {
  opacity: 1;
  color: #ffbd00;
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

@keyframes pop {
  0% { transform: scale(0); }
  70% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
</style>


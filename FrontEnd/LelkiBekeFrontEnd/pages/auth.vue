<script setup lang="ts">
  const route = useRoute()
  const isLogin = ref(!route.query.register)
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const auth = useAuthStore()
  const showPassword = ref(false);
  
  // Get redirect path from query parameters or use default
  const redirectPath = computed(() => {
    return (route.query.redirect as string) || '/'
  })
  
  const handleLogin = async () => {
    const success = await auth.login(email.value, password.value)

    if (success && auth.user) {
      // Debug log the user and role
      console.log('Login successful, user:', auth.user)
      console.log('User role:', auth.user.role)
      
      // Role-based redirection with explicit string comparison
      const role = auth.user.role?.toLowerCase().trim();
      
      if (role === 'waiter') {
        console.log('Redirecting to waiter page')
        await navigateTo('/waiter');
        return; // Add early return to prevent further execution
      } 
      else if (role === 'kitchen') {
        console.log('Redirecting to kitchen page')
        await navigateTo('/kitchen');
        return; // Add early return to prevent further execution
      } 
      else {
        console.log('No special role, using default redirection')
        // Retrieve the intended URL from localStorage or use default route
        const intendedUrl = localStorage.getItem('intendedUrl');
        if (intendedUrl) {
          localStorage.removeItem('intendedUrl');
          await navigateTo(intendedUrl);
        } else {
          await navigateTo('/');
        }
      }
    } else {
      console.error('Login failed or user missing')
    }
  }

  const submitForm = async () => {
    if (isLogin.value) {
      await handleLogin()
    } else {
      const success = await auth.register(name.value, email.value, password.value)
      if (success && auth.user) {
        navigateTo(redirectPath.value)
      }
    }
  }

  const getTranslatedError = computed(() => {
    if (!auth.error) return '';
    
    // Add translations for common errors
    const errorMap: Record<string, string> = {
      'Hibás e-mail vagy jelszó': 'Invalid email or password',
      'The email field is required': 'Email is required',
      'The password field is required': 'Password is required'
    };
    
    return errorMap[auth.error] || auth.error;
  });

  import PasswordEyeIcon from '~/components/PasswordEyeIcon.vue'
</script>

<template>
  <div class="auth-page">
    <div class="auth-container fade-in-bottom">
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

        <div class="error-message" v-if="auth.error">
          {{ getTranslatedError }}
        </div>

        <button type="submit" :disabled="auth.loading" class="glow-button">
          {{ auth.loading ? 'Processing...' : isLogin ? 'Login' : 'Register' }}
        </button>

      </form>

      <button @click="isLogin = !isLogin" class="toggle-button">
        <span class="text-center w-100 d-block">{{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}</span>
      </button>
      <button @click="navigateTo('/')" class="home-button">
        <span class="button-text">Back to Home</span>
      </button>
      <!-- <ButtonComponet text="Click me!" style="margin-top: 5px;"/> -->
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
  background-color: rgba(220, 53, 69, 0.1);
  padding: 10px;
  border-radius: 8px;
  border-left: 4px solid #dc3545;
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
</style>


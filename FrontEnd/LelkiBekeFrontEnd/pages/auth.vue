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

  import PasswordEyeIcon from '~/components/PasswordEyeIcon.vue'
</script>

<template>
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

      <div class="error-message" v-if="auth.error">{{ auth.error }}</div>

      <button type="submit" :disabled="auth.loading" class="glow-button">
        {{ auth.loading ? 'Processing...' : isLogin ? 'Login' : 'Register' }}
      </button>

    </form>

    <button @click="isLogin = !isLogin" class="toggle-button">
      <span class="text">{{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}</span>
    </button>
    <button @click="navigateTo('/')" class="back-button">
      Back to Home
    </button>
    <!-- <ButtonComponet text="Click me!" style="margin-top: 5px;"/> -->
  </div>
</template>

<style scoped>
@import url("~/assets/css/main.css");

.auth-container {
  max-width: 400px;
  margin: auto;
  padding: 2rem;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
  border-radius: 10px;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
}

button {
  width: 100%;
  padding: 0.75rem;
  background: #9E7E5E;
  color: black;
  border: none;
  cursor: pointer;
  transition: opacity 0.25s ease, background-color 0.25s ease; /* Smooth transition */
}



.toggle-button {
  margin-top: 1rem;
  background: none;
  color: black;
  text-decoration: underline;
}

.toggle-button:hover{
  color: burlywood;
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

.back-button {
  margin-top: 1rem;
  background: #6c757d;
  color: white;
  border-radius: 5px;
}

.back-button:hover {
  background: #5a6268;
}

</style>


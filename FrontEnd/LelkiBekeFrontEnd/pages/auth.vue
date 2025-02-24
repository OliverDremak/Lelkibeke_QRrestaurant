<script setup lang="ts">
  const route = useRoute()
  const isLogin = ref(!route.query.register)
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const auth = useAuthStore()
  
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
          <input v-model="password" type="password" class="form-control" id="floatingInput2" placeholder="">
          <label for="floatingInput2">Password</label>
      </div>

      <div class="error-message" v-if="auth.error">{{ auth.error }}</div>

      <button type="submit" :disabled="auth.loading" class="glow-button">
        {{ auth.loading ? 'Processing...' : isLogin ? 'Login' : 'Register' }}
      </button>

    </form>

    <button @click="isLogin = !isLogin" class="toggle-button">
      <span class="text">{{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}</span>
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


</style>


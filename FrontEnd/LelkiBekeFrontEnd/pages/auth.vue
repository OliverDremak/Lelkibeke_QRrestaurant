<script setup lang="ts">
const isLogin = ref(true)
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const auth = useAuthStore()
  
  const submitForm = async () => {
    if (isLogin.value) {
      await auth.login(email.value, password.value)
    } else {
      await auth.register(name.value, email.value, password.value)
    }
    
    if (auth.user) {
      navigateTo('/menu')
      window.location.reload()
    }
  }

</script>

<template>
    <div class="auth-container">
      <h1>{{ isLogin ? 'Login' : 'Register' }}</h1>
      
      <form @submit.prevent="submitForm">
        <div v-if="!isLogin" class="form-group">
          <label>Name</label>
          <input v-model="name" type="text" required>
        </div>
        
        <div class="form-group">
          <label>Email</label>
          <input v-model="email" type="email" required>
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" required>
        </div>
        
        <div class="error-message" v-if="auth.error">{{ auth.error }}</div>
        
        <button type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Processing...' : isLogin ? 'Login' : 'Register' }}
        </button>
      </form>
      
      <button @click="isLogin = !isLogin" class="toggle-button">
        {{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}
      </button>
    </div>
</template>

<style scoped>
.auth-container {
    max-width: 400px;
    margin: 2rem auto;
    padding: 2rem;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
  }
  
  button:disabled {
    background: #cccccc;
  }
  
  .toggle-button {
    margin-top: 1rem;
    background: none;
    color: #007bff;
    text-decoration: underline;
  }
  
  .error-message {
    color: red;
    margin-bottom: 1rem;
  }
</style>
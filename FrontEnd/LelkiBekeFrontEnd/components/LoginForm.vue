<template>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title text-center">Login</h3>
              <form @submit.prevent="handleLogin">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input
                    type="email"
                    id="email"
                    v-model="email"
                    class="form-control"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="password">Password</label>
                  <input
                    type="password"
                    id="password"
                    v-model="password"
                    class="form-control"
                    required
                  />
                </div>
                <div class="form-group form-check">
                  <input
                    type="checkbox"
                    id="rememberMe"
                    v-model="rememberMe"
                    class="form-check-input"
                  />
                  <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-3">
                  Login
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  
  const email = ref('')
  const password = ref('')
  const rememberMe = ref(false)
  const router = useRouter()
  
  const handleLogin = () => {
    // Add your login logic here (e.g., API call)
    // For demonstration, we'll just redirect to the home page
    if (email.value && password.value) {
      router.push('/')
    } else {
      alert('Please enter your email and password')
    }
  }
  </script>
  
  <style scoped>
  .container {
    max-width: 600px;
  }
  .card {
    margin-top: 50px;
  }
  </style>
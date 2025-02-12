<template>
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title text-center">Login</h3>
              <form @submit.prevent="login">
                <div v-if="errorMessage" class="alert alert-danger">
                  {{ errorMessage }}
                </div>
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

interface LoginResponse {
  user: {
    id: string;
    name: string;
    email: string;
    // Add other user properties as needed
  };
  // Add other response properties as needed
}
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const rememberMe = ref(false);

const login = async () => {
  try {
    const { data, error } = await useFetch<LoginResponse>('/api/login', {
      method: 'POST',
      body: {
        email: email.value,
        password: password.value,
        remember_me: rememberMe.value
      }
    });

    if (error.value) {
      errorMessage.value = error.value.data?.message || 'Login failed';
      return;
    }

    if (data.value) {
      // Simple session storage (lasts until browser closes)
      sessionStorage.setItem('user', JSON.stringify(data.value.user));
      await navigateTo('/menu');
    }
  } catch (err) {
    errorMessage.value = 'Connection error';
  }
};
</script>
  
  <style scoped>
  .container {
    max-width: 600px;
  }
  .card {
    margin-top: 50px;
  }
  </style>
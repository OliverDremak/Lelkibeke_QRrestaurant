<template>
    <div class="container d-flex justify-content-center align-items-center mt-5">
      <form @submit.prevent="register" class="card p-4 shadow-lg" style="width: 24rem;">
        <h3 class="text-center mb-4">Register</h3>
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input v-model="name" type="text" id="name" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input v-model="email" type="email" id="email" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input v-model="password" type="password" id="password" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">Role</label>
          <select v-model="role" id="role" class="form-select" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
    </div>
  </template>
  
  <script setup lang="ts">
import { ref } from 'vue';

const name = ref('');
const email = ref('');
const password = ref('');
const role = ref('user');
const errorMessage = ref('');
const successMessage = ref('');

const api = useApi();

const register = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await api.register(name.value, email.value, password.value);
    successMessage.value = 'User registered successfully!';
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.errors) {
      errorMessage.value = Object.values(error.response.data.errors)
        .flat()
        .join(' ');
    } else {
      errorMessage.value = 'Registration failed!';
    }
  }
};
</script>
  <style scoped>
  </style>
  
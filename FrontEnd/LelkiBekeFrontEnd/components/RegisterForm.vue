<template>
    <div class="container d-flex justify-content-center align-items-center mt-5">
      <form @submit.prevent="register" class="card p-4 shadow-lg" style="width: 24rem;">
        <h3 class="text-center mb-4">Register</h3>
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input v-model="form.name" type="text" id="name" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input v-model="form.email" type="email" id="email" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input v-model="form.password" type="password" id="password" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">Role</label>
          <select v-model="form.role" id="role" class="form-select" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'user'
  });
  
  const register = async () => {
    try {
      const response = await fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form.value)
      });
      if (!response.ok) throw new Error('Registration failed');
      router.push('/login');
    } catch (error) {
      console.error(error);
    }
  };
  </script>
  
  <style scoped>
  </style>
  
<script setup lang="ts">
  definePageMeta({
    requiresAuth: false
  })
</script>
<template>
    <div class="thank-you-container">
      <div class="thank-you-card">
        <h1>Thank You</h1>
        <p>for your order</p>
        <div class="divider"></div>
        <div class="social-links">
          <div>
            <a href="https://facebook.com/name" target="_blank">Follow Us</a>
          </div>
          <div>
            <a href="https://reviewsite.com/url" target="_blank">Give Feedback</a>
          </div>
        </div>
        <NuxtLink to="/" class="return-button">Return to Main</NuxtLink>
      </div>
    </div>
  </template>
  
  <style scoped>
  .thank-you-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #D7CCC8;
  }
  
  .thank-you-card {
    text-align: center;
    background-color: #E8F5E9;
    padding: 3rem;
    border-radius: 10px;
    max-width: 600px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    font-family: 'Cursive', sans-serif;
    min-width: 450px;
  }
  
  h1 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #2E7D32;
  }
  
  p {
    font-size: 1.5rem;
    color: #333;
  }
  
  .divider {
    width: 50%;
    height: 2px;
    background-color: black;
    margin: 1rem auto;
  }
  
  .social-links {
    display: flex;
    justify-content: space-around;
    margin-top: 1.5rem;
    font-size: 1.2rem;
  }
  
  .social-links a {
    color: #2E7D32;
    text-decoration: none;
  }
  
  .social-links a:hover {
    text-decoration: underline;
  }
  
  .return-button {
    display: inline-block;
    margin-top: 2rem;
    padding: 0.75rem 1.5rem;
    background-color: #8BC34A;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s ease;
  }
  
  .return-button:hover {
    background-color: #7CB342;
  }
  </style>
  
  
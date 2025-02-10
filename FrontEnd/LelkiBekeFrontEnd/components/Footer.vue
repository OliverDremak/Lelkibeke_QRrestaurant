<template>
  <div class="container">
    <div class="row">
    <!-- About Devs Section -->
    <div class="col-12 col-md-6 text-center mb-4">
      <h2>About Devs</h2>
      <div class="row justify-content-center g-4">
        <div 
          v-for="(profile, index) in profiles"
          :key="index"
          class="col-4 d-flex justify-content-center"
        >
          <div 
            class="profile-card text-center"
            :class="{ 'blur': hoveredIndex !== null && hoveredIndex !== index }"
            @mouseover="hoveredIndex = index"
            @mouseleave="hoveredIndex = null"
            @click="openGithub(profile.username)" 
          >
            <img 
              :src="profile.image"
              :alt="profile.name"
              class="img-fluid rounded-circle mb-3 shadow"
              style="width: 60px; height: 60px; object-fit: cover;" 
            >
            <h5 class="mb-0">{{ profile.name }}</h5>
            <small :class="{ 'text-hidden': hoveredIndex === null || hoveredIndex !== index }">@{{ profile.username }}</small>
          </div>
        </div>
      </div>
      <div class="row">
        <h3>Konzulens</h3>
        <h5>Bolya Gábor</h5>
      </div>
    </div>
    <!-- Contact Us Section -->
    <div class="col-12 col-md-6 text-center mb-4">
      <h2>Contact Us</h2>
      <p>If you have any questions or feedback, feel free to reach out to us!</p>
      <form @submit.prevent="submitContactForm">
        <div class="mb-3">
          <input type="text" class="form-control" v-model="contactForm.name" placeholder="Your Name" required>
        </div>
        <div class="mb-3">
          <input type="email" class="form-control" v-model="contactForm.email" placeholder="Your Email" required>
        </div>
        <div class="mb-3">
          <textarea class="form-control" v-model="contactForm.message" placeholder="Your Message" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
      </form>
    </div>
  </div>
  </div>
  
</template>

<script setup>
import { ref, reactive } from 'vue';

const profiles = ref([
        { 
          name: 'Dremák Olivér',
          username: 'OliverDremak',
          image: 'https://github.com/OliverDremak.png'
        },
        { 
          name: 'Kiss Marcell',
          username: 'kissmarcell132',
          image: 'https://github.com/kissmarcell132.png'
        },
        { 
          name: 'Korsós Erik',
          username: 'korsoserik',
          image: 'https://github.com/korsoserik.png'
        },
      ]);

const hoveredIndex = ref(null);

const openGithub = (username) => {
  window.open(`https://github.com/${username}`, '_blank');
};

const contactForm = reactive({
  name: '',
  email: '',
  message: ''
});

const submitContactForm = () => {
  // Handle form submission logic here
  console.log('Contact Form Submitted:', contactForm);
};
</script>

<style scoped>
.container{
  background-color: burlywood;
  border-top-left-radius: 40px;
  border-top-right-radius: 40px;
}
.profile-card {
  cursor: pointer;
  transition: transform 0.3s, opacity 0.3s;
}
.profile-card.blur {
  opacity: 0.5;
}
.profile-card:hover {
  transform: scale(1.05);
}
body, html {
  overflow-x: hidden;
}
.text-hidden{
  color: burlywood;
}
</style>
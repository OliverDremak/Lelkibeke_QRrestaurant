<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '~/stores/auth';

const auth = useAuthStore();

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
  {
    name: 'Bólya Gábor',
    username: 'bolyagabor',
    image: 'https://github.com/bolyagabor.png'
  }
]);

const openingHours = ref([]);
const hoveredIndex = ref(null);

const openGithub = (username) => {
  window.open(`https://github.com/${username}`, '_blank');
};


const contactForm = reactive({
  name: auth.user ? auth.user.name : '',
  email: auth.user ? auth.user.email : '',
  message: ''
});

const submitContactForm = async () => {
  try {
    await axios.post('http://localhost:8000/api/contact-messages', contactForm);
    alert('Message sent successfully!');
    // Reset form
    contactForm.name = auth.user ? auth.user.name : '';
    contactForm.email = auth.user ? auth.user.email : '';
    contactForm.message = '';
  } catch (error) {
    console.error('Error sending message:', error);
    alert('Failed to send message. Please try again.');
  }
};

const fetchOpeningHours = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/opening-hours');
    openingHours.value = response.data;
  } catch (error) {
    console.error('Error fetching opening hours:', error);
  }
};

watch(() => auth.user, (newUser) => {
  if (newUser) {
    contactForm.name = newUser.name;
    contactForm.email = newUser.email;
  } else {
    contactForm.name = '';
    contactForm.email = '';
  }
});

onMounted(fetchOpeningHours);
</script>

<template>
  <div class="container-fluid footer">
    <div class="container">
      <div class="row">
        <div class="col-12 col-lg-4 col-md-6 text-center mb-4 border-right">
          <h2 class="heading">About Devs</h2>
          <div class="row justify-content-center g-4">
            <div v-for="(profile, index) in profiles" :key="index" class="col-4 d-flex justify-content-center">
              <div>
                <div class="profile-card text-center" :class="{ 'blur': hoveredIndex !== null && hoveredIndex !== index }" 
                     @mouseover="hoveredIndex = index" @mouseleave="hoveredIndex = null" @click="openGithub(profile.username)">
                  <img :src="profile.image" :alt="profile.name" class="img-fluid rounded-circle mb-3 shadow"
                       style="width: 60px; height: 60px; object-fit: cover;">
                  <h5 class="mb-0">{{ profile.name }}</h5>
                  <small :class="{ 'text-hidden': hoveredIndex === null || hoveredIndex !== index }">@{{ profile.username }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4 col-md-6 text-center">
          <h2 class="heading ul-caption">Opening Hours</h2>
          <ul class="list-unstyled">
            <li v-for="entry in openingHours" :key="entry.id">
              <strong>{{ entry.day_of_week }}:</strong>
              <span v-if="entry.is_closed">Closed</span>
              <span v-else>{{ entry.open_time.substring(0,5) }} - {{ entry.close_time.substring(0,5) }}</span>
            </li>
          </ul>
        </div>
        <div class="col-12 col-lg-4 col-md-12 text-center last">
          <h2 class="heading">Contact Us</h2>
          <p>If you have any questions or feedback, feel free to reach out to us!</p>
          <form @submit.prevent="submitContactForm">
            <div class="mb-3">
              <input type="text" class="form-control" v-model="contactForm.name" placeholder="Your Name" required :readonly="auth.user !== null">
            </div>
            <div class="mb-3">
              <input type="email" class="form-control" v-model="contactForm.email" placeholder="Your Email" required :readonly="auth.user !== null">
            </div>
            <div class="mb-3">
              <textarea class="form-control" v-model="contactForm.message" placeholder="Your Message" rows="3" required></textarea>
            </div>
            <ButtonComponet text="Send Message" style="margin-top: 5px;" />
          </form>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
@import url("~/assets/css/main.css");

.footer {
  background-color: #f9f9f9;
  transition: background-color 0.3s ease;
}

:root.dark .footer {
  background-color: #1a1a1a;
  color: #e0e0e0;
}

.profile-card {
  cursor: pointer;
  transition: transform 0.3s, opacity 0.3s;
}

.profile-card:last-child {
  margin-bottom: 20px;
}

@media (min-width: 767px) {
  .border-right {
    border-right: 1px solid #00000024;
  }

  :root.dark .border-right {
    border-right: 1px solid #ffffff24;
  }
}

@media (max-width: 765px) {
  .border-right {
    border-bottom: 1px solid #00000024;
  }

  :root.dark .border-right {
    border-bottom: 1px solid #ffffff24;
  }
}

@media (max-width: 991px) {
  .last {
    border-top: 1px solid #00000024;
    padding-top: 15px;
    
  }

  :root.dark .last {
    border-top: 1px solid #ffffff24;
  }
}
@media (min-width: 992px) {
  .last {
    border-left: 1px solid #00000024;

  }

  :root.dark .last {
    border-left: 1px solid #ffffff24;
  }
}

li {
  padding: 10px;
  letter-spacing: 3px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  text-transform: uppercase;
}

.ul-caption {
  margin-bottom: 40px;
}

.profile-card.blur {
  opacity: 0.5;
}

.profile-card:hover {
  transform: scale(1.05);
}

.text-hidden {
  color: burlywood;
}

.form-control {
  transition: background-color 0.3s ease, color 0.3s ease;
}

:root.dark .form-control {
  background-color: #2d2d2d;
  color: #e0e0e0;
  border-color: #404040;
}

:root.dark .form-control::placeholder {
  color: #808080;
}
</style>
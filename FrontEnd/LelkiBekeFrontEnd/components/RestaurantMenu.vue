<template>
  <div class="menu-container container" :class="{ 'dark': isDarkMode }">
    <div v-if="loading" class="text-center mt-4">
      <p>{{ t('reastaurantMenu.parLoad') }}</p>
    </div>
    <div v-else-if="!mainCourses.length" class="text-center mt-4">
      <p>{{ t('reastaurantMenu.parNoItems') }}</p>
    </div>
    <div v-else class="menu-content">
      <div class="category-container-wrapper">
        <div ref="categoryContainer" class="category-buttons">
          <button 
            @click="filterByCategory('')"
            :class="{ active: selectedCategory === '' }"
          >
            All
          </button>
          <button 
            v-for="category in uniqueCategories" 
            :key="category" 
            @click="filterByCategory(category)"
            :class="{ active: selectedCategory === category }"
          >
            {{ category }}
          </button>
        </div>
      </div>
      <div class="main-content row">
        <!-- Menu Section -->
        <div class="menu-section" :class="menuClass">
          <h2>Main Courses</h2>
          <div class="menu-grid">
            <div v-for="item in filteredMainCourses" :key="item.id" class="menu-item">
              <img class="menu-item-image" src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fimages.deliveryhero.io%2Fimage%2Ffd-hu%2FLH%2Fhvmf-hero.jpg&f=1&nofb=1&ipt=3f262fe518822ce6b005b76e32553af53eb53e599d9891c6a15efd23cad0747e&ipo=images" alt="">
              <h3>{{ item.name }} ({{ item.category_name }})</h3>
              <p>{{ item.description }}</p>
              <p class="price">{{ item.price }}ft</p>
              <ButtonComponet @click="addToCart(item)" :text="t('reastaurantMenu.addToCart')"/>
            </div>
          </div>
        </div>

        <div class="cart-section col-xl-4 col-lg-6" :class="{ expanded: isCartExpanded }">
          <h2 class="heading orderheading text-center mb-3">{{ t('reastaurantMenu.cartTitle') }}</h2>
          <div v-if="cart.length === 0">{{ t('reastaurantMenu.cartEmpty') }}</div>
          <div v-else>
            <div v-for="(cartItem, index) in cart" :key="index" class="cart-item row">
              <span class="col-5">{{ cartItem.name }}</span>
              <span class="col-3">{{ (cartItem.price * cartItem.quantity).toFixed(2) }}ft</span>
              <div class="quantitybuttons col-2 text-center">
                <button @click="decreaseQuantity(index)"><img src="../public/svgs/minus.svg" alt="Trash" class="svg-icon" width="24" height="24"></button>
                <span style="margin-top: 2px;">{{ cartItem.quantity }}</span>
                <button @click="increaseQuantity(index)"><img src="../public/svgs/plus.svg" alt="Trash" class="svg-icon" width="24" height="24"></button>
              </div>
              <button @click="removeFromCart(index)" class="trash col-2"><img src="../public/svgs/trash.svg" alt="Trash" class="svg-icon" width="24" height="24"></button>
              <input v-model="cartItem.notes" placeholder="Add a note" class="col-12 mt-2"/>
            </div>
            <hr>
            <div class="total">
              <span class="heading">{{ t('reastaurantMenu.total') }}: {{ cartTotal }}ft</span>
              <ButtonComponet @click="handleCheckout" :text="t('reastaurantMenu.checkout')" class="totheright"/>
            </div>
          </div>
          <button class="toggle-cart" @click="toggleCart">
            {{ isCartExpanded ? '▼' : '▲' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ButtonComponet from './ButtonComponet.vue';
import axios from 'axios';
import { useI18n } from '#imports'
const { t } = useI18n()

const mainCourses = ref([]);
const loading = ref(true);

const isWide = ref(false);
const menuClass = computed(() => ({
  "menu-section": true,
  "col-xl-8 col-lg-6 col-md-12": isWide.value,
  "col-12": !isWide.value,
}));
const handleResize = () => {
  isWide.value = window.innerWidth >= 1000;
};

const isDarkMode = computed(() => localStorage.getItem('darkMode') === 'true');

onMounted(async () => {
  handleResize();
  window.addEventListener("resize", handleResize);
  try {
    console.log('RestaurantMenu mounting with tableId:', props.tableId);
    const response = await axios.get('https://api.innerpeace.jedlik.cloud/api/menu');
    console.log('Menu data received:', response.data);
    mainCourses.value = response.data;
  } catch (error) {
    console.error('Error loading menu:', error);
  } finally {
    loading.value = false;
  }
});

const props = defineProps({
  tableId: {
    type: Number,
    required: false,
  },
});

onUnmounted(() => {
  window.removeEventListener("resize", handleResize);
});

const cart = ref([]);
if (typeof window !== 'undefined') {
  cart.value = JSON.parse(localStorage.getItem('cart')) || [];
}
const isCartExpanded = ref(false);
const selectedCategory = ref('');
const filteredMainCourses = computed(() => {
  console.log('Filtering courses:', mainCourses.value);
  return selectedCategory.value
    ? mainCourses.value.filter(item => item.category_name === selectedCategory.value)
    : mainCourses.value;
});

const uniqueCategories = computed(() => {
  const categories = mainCourses.value.map(item => item.category_name);
  return [...new Set(categories)];
});

const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + item.price * item.quantity, 0);
});

const addToCart = (item) => {
  const cartItem = cart.value.find(ci => ci.id === item.id);
  if (cartItem) {
    cartItem.quantity++;
  } else {
    cart.value.push({ ...item, quantity: 1, notes: '' });
  }
};

const removeFromCart = (index) => {
  cart.value.splice(index, 1);
};

const increaseQuantity = (index) => {
  cart.value[index].quantity++;
};

const decreaseQuantity = (index) => {
  if (cart.value[index].quantity > 1) {
    cart.value[index].quantity--;
  } else {
    removeFromCart(index);
  }
};

const toggleCart = () => {
  isCartExpanded.value = !isCartExpanded.value;
};

const handleCheckout = async () => {
  const auth = useAuthStore();
  const token = auth.token;
  console.log('Retrieved token:', token);

  if (!token) {
    // Store the intended URL before redirecting to the login page
    localStorage.setItem('intendedUrl', window.location.pathname);
    alert('Please login first');
    router.push(`/auth?redirect=${encodeURIComponent(currentPath)}`);
    return;
  }

  const orderData = {
    table_id: props.tableId,
    total_price: cartTotal.value,
    order_items: cart.value.map(item => ({
      menu_item_id: item.id,
      quantity: item.quantity,
      notes: item.notes || ''
    }))
  };

  try {
    const response = await fetch('https://api.innerpeace.jedlik.cloud/api/sendOrder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(orderData)
    });

    console.log('Response status:', response.status);

    if (!response.ok) {
      const errorData = await response.json();
      if (response.status === 401) {
        localStorage.removeItem('token');
        await navigateTo('/auth');
        return;
      }
      throw new Error(errorData.message || 'Error placing order');
    }

    const responseData = await response.json();
    if (responseData.order_id) {
      alert('Order placed successfully!');
      localStorage.removeItem('cart');
      await navigateTo('/thankyou');
    }
  } catch (error) {
    console.error('Checkout error:', error);
    if (error.message.includes('Unauthorized')) {
      localStorage.removeItem('token');
      await navigateTo('/auth');
    } else {
      alert('Error placing order: ' + error.message);
    }
  }
};

const filterByCategory = (category) => {
  selectedCategory.value = category;
};

watch(cart, (newCart) => {
  localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

</script>

<style scoped>
@import url("~/assets/css/main.css");

.trash .svg-icon {
  filter: brightness(0) saturate(100%) invert(30%) sepia(100%) saturate(1000%) hue-rotate(0deg);
}

.trash {
  border-radius: 50px;
  width: 55px; /* Rounded corners for the trash button */
}

.totheright {
  float: right;
}

button:hover .svg-icon {
  filter: brightness(0) saturate(100%) invert(0%) sepia(0%) saturate(100%) hue-rotate(0deg);
}

.quantitybuttons {
  display: flex;
  justify-content: center;
  gap: 2px;
}

.quantitybuttons * {
  border: none;
  background-color: transparent;
}

.quantitybuttons button {
  cursor: pointer;
}

.menu-container {
  padding: 20px;
}

/* Category Selector Styles */
.category-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  overflow-x: auto;
  white-space: nowrap;
  scroll-behavior: smooth;
  padding: 0 40px;
}

/* Update Category Button Styles */
.category-buttons button {
  padding: 10px 20px;
  cursor: pointer;
  border: none;
  border-radius: 30px;
  background: linear-gradient(145deg, #ffffff, #e6e6e6);
  transition: all 0.3s ease;
  white-space: nowrap;
  font-weight: bold;
  color: #333;
}

.category-buttons button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.category-buttons button.active {
  background: linear-gradient(145deg, #dd6013, #ffbd00);
  color: white;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

.menu-item-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 15px;
}

.category-container-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.category-buttons {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.category-buttons::-webkit-scrollbar {
  display: none;
}

.scroll-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.8);
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  font-size: 20px;
  color: #333;
  cursor: pointer;
  display: none;
}

.scroll-button.left {
  left: 5px;
}

.scroll-button.right {
  right: 5px;
}

@media (max-width: 768px) {
  .scroll-button {
    display: block;
  }
}

.main-content {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  width: 100%;
}

.menu-item {
  border: 1px solid #e0e0e0;
  background-color: #fff;
  padding: 20px;
  border-radius: 12px;
  width: calc(33.333% - 20px);
  box-sizing: border-box;
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.menu-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.menu-item h3 {
  font-size: 1.25rem;
  margin-bottom: 10px;
  color: #333;
}

.menu-item p {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 15px;
}

.menu-item .price {
  font-size: 1.1rem;
  font-weight: bold;
  color: #dd6013;
  margin-bottom: 15px;
}

.menu-item button {
  background: linear-gradient(145deg, #dd6013, #ffbd00);
  border: none;
  border-radius: 25px;
  padding: 10px 20px;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.menu-item button:hover {
  background: linear-gradient(145deg, #ffbd00, #dd6013);
  transform: scale(1.05);
}

.menu-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
}

@media (max-width: 1200px) {
  .menu-item {
    width: calc(50% - 20px);
  }
}

@media (max-width: 999px) {
  .menu-item {
    width: calc(33.333% - 20px);
  }
}

@media (max-width: 768px) {
  .menu-item {
    width: calc(50% - 20px);
  }
}

@media (max-width: 480px) {
  .menu-item {
    width: 100%;
  }
}

.cart-section {
  flex: 1;
  background: #f5f5f5;
  padding: 1rem;
  border-radius: 25px; /* Rounded corners */
  box-shadow: 9px 9px 12px #b2b2b3, inset 0 0 7px rgba(0, 0, 0, 0.3);
  max-height: fit-content;
}

.cart-item {
  align-items: center;
  margin-bottom: 20px;
}

.cart-item input {
  border-radius: 25px; /* Rounded corners for the input field */
  padding: 10px;
  border: 1px solid #ccc;
}

.total {
  font-weight: bold;
  margin-top: 10px;
  margin-left: 3px;
}

.toggle-cart {
  margin-top: 10px;
}

@media (max-width: 999px) {
  .cart-section {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding-left: 35px;
    z-index: 1000;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    transform: translateY(calc(70% - 40px));
    width: 100%;
    min-height: 25vh;
    border-radius: 25px 25px 0 0; /* Rounded top corners */
  }

  .cart-section.expanded {
    transform: translateY(0);
  }

  .cart-section h2,
  .cart-section .cart-item,
  .cart-section hr {
    display: none;
  }

  .cart-section.expanded h2,
  .cart-section.expanded .cart-item,
  .cart-section.expanded hr {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .cart-section .total {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .orderheading {
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
  }

  .toggle-cart {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #ccc;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
  }
}

@media (min-width: 1000px) {
  .toggle-cart {
    display: none;
  }
}

/* Update dark mode styles */
:root.dark .menu-container {
  color: #ffffff;
}

:root.dark .menu-item {
  background-color: #2d2d2d;
  border-color: #404040;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

:root.dark .menu-item:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}

:root.dark .menu-item h3,
:root.dark .menu-item p {
  color: #ffffff;
}

:root.dark .cart-section {
  background-color: #2d2d2d;
  color: #ffffff;
  box-shadow: 9px 9px 12px #000000, inset 0 0 7px rgba(255, 255, 255, 0.1);
}

:root.dark .category-buttons button {
  background: linear-gradient(145deg, #2d2d2d, #1a1a1a);
  color: #ffffff;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

:root.dark .category-buttons button:hover {
  background: linear-gradient(145deg, #333333, #2d2d2d);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
}

:root.dark .category-buttons button.active {
  background: linear-gradient(145deg, #dd6013, #ffbd00);
  color: white;
  box-shadow: 
    inset 0 2px 4px rgba(0, 0, 0, 0.3),
    0 0 15px rgba(221, 96, 19, 0.3);
}

:root.dark .cart-item input {
  background-color: #404040;
  border-color: #666;
  color: #ffffff;
}

:root.dark hr {
  border-color: #404040;
}

:root.dark .toggle-cart {
  background-color: #2d2d2d;
  color: #ffffff;
  border-color: #404040;
}
</style>
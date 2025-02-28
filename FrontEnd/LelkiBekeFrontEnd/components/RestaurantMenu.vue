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
        <!-- Menu Section - Removed "Main Courses" heading -->
        <div class="menu-section" :class="menuClass">
          <!-- h2>Main Courses</h2> removed -->
          <div class="menu-grid">
            <div v-for="item in filteredMainCourses" :key="item.id" class="menu-item">
              <div class="menu-item-content">
                <img class="menu-item-image" :src="item.image_url" :alt="item.image_url">
                <h3>{{ item.name }}</h3>
                <div class="description-container">
                  <p>{{ item.description }}</p>
                </div>
                <div class="menu-item-footer">
                  <p class="price">{{ item.price }}ft</p>
                  <ButtonComponet @click="addToCart(item)" :text="t('reastaurantMenu.addToCart')"/>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="cart-section col-xl-4 col-lg-6" 
             :class="{ 
               expanded: isCartExpanded, 
               'footer-visible': isNearFooter 
             }">
          <div class="cart-header">
            <h2>{{ t('reastaurantMenu.cartTitle') }}</h2>
            <div class="cart-count" v-if="cart.length">
              {{ cart.length }}
            </div>
          </div>
          
          <div v-if="cart.length === 0" class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <p>{{ t('reastaurantMenu.cartEmpty') }}</p>
          </div>
          
          <div v-else class="cart-content">
            <div class="cart-items">
              <div v-for="(cartItem, index) in cart" :key="index" class="cart-item">
                <div class="item-details">
                  <div class="item-name-price">
                    <h4>{{ cartItem.name }}</h4>
                    <span class="item-price">{{ (cartItem.price * cartItem.quantity).toFixed(0) }}ft</span>
                  </div>
                  <div class="item-controls">
                    <div class="quantity-control">
                      <button @click="decreaseQuantity(index)" class="quantity-btn">
                        <img src="../public/svgs/minus.svg" alt="-" class="svg-icon" width="16" height="16">
                      </button>
                      <span class="quantity-value">{{ cartItem.quantity }}</span>
                      <button @click="increaseQuantity(index)" class="quantity-btn">
                        <img src="../public/svgs/plus.svg" alt="+" class="svg-icon" width="16" height="16">
                      </button>
                    </div>
                    <button @click="removeFromCart(index)" class="remove-btn">
                      <img src="../public/svgs/trash.svg" alt="Remove" class="svg-icon" width="18" height="18">
                    </button>
                  </div>
                </div>
                <textarea 
                  v-model="cartItem.notes" 
                  :placeholder="t('reastaurantMenu.specialRequest')"
                  class="item-notes"
                  rows="1"
                ></textarea>
              </div>
            </div>
            
            <div class="cart-footer">
              <div class="cart-total">
                <span class="total-label">{{ t('reastaurantMenu.total') }}</span>
                <span class="total-amount">{{ cartTotal }}ft</span>
              </div>
              <ButtonComponet 
                @click="handleCheckout" 
                :text="isSubmitting ? 'Processing...' : t('reastaurantMenu.checkout')"
                :disabled="isSubmitting"
                class="checkout-btn"
              />
            </div>
          </div>
          
          <button class="toggle-cart" @click="toggleCart">
            <div class="toggle-icon">
              <span></span>
              <span></span>
            </div>
          </button>
          
          <!-- Add a minimal indicator that shows when near footer -->
          <div class="cart-mini-indicator" v-if="isNearFooter && !isCartExpanded && cart.length > 0">
            <span class="mini-count">{{ cart.length }}</span>
            <span class="mini-total">{{ cartTotal }}ft</span>
            <span class="mini-arrow">↑</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
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

// Add a loading state
const isSubmitting = ref(false);

const handleCheckout = async () => {
  // Prevent multiple submissions
  if (isSubmitting.value) return;
  
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

  try {
    // Set submitting state to true
    isSubmitting.value = true;
    
    // ...rest of the existing checkout code...
    
    const orderData = {
      table_id: props.tableId,
      total_price: cartTotal.value,
      order_items: cart.value.map(item => ({
        menu_item_id: item.id,
        quantity: item.quantity,
        notes: item.notes || ''
      }))
    };

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
      localStorage.removeItem('cart');
      await navigateTo('/thankyou');
    }
  } catch (error) {
    console.error('Checkout error:', error);
    // Reset submitting state on error
    isSubmitting.value = false;
    
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

// Add state for footer visibility
const isNearFooter = ref(false);

onMounted(() => {
  // ...existing onMounted code...
  
  // Set up intersection observer to detect when near footer
  if (typeof window !== 'undefined' && 'IntersectionObserver' in window) {
    // Create observer for footer detection
    const footerObserver = new IntersectionObserver((entries) => {
      // When footer becomes visible, adjust cart position
      isNearFooter.value = entries[0].isIntersecting;
    }, {
      rootMargin: '100px',
      threshold: 0.1
    });
    
    // Observe the footer element
    const footerElement = document.querySelector('.footer');
    if (footerElement) {
      footerObserver.observe(footerElement);
    }
  }
});
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
  margin-bottom: 10px; /* Reduced from 20px to 10px */
  overflow-x: auto;
  white-space: nowrap;
  scroll-behavior: smooth;
  padding: 0 40px 5px; /* Reduced bottom padding from 10px to 5px */
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
  padding-top: 0; /* Reduced from 5px to 0 */
  overflow: visible; /* Ensure content isn't clipped */
  margin-top: 0; /* Add this to ensure no extra spacing */
}

.category-buttons {
  -ms-overflow-style: none;
  scrollbar-width: none;
  padding: 5px 40px 10px; /* Add top padding to container */
  overflow-y: visible; /* Allow vertical overflow for hover effect */
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

/* Enhanced Menu Item Styling */
.menu-item {
  border: none;
  background-color: #fff;
  padding: 0;
  border-radius: 16px;
  width: calc(33.333% - 20px);
  box-sizing: border-box;
  transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}

.menu-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.menu-item:active {
  transform: translateY(-2px);
}

.menu-item-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  height: 100%;
}

.menu-item-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 0;
  margin-bottom: 0;
  transition: transform 0.5s ease;
}

.menu-item:hover .menu-item-image {
  transform: scale(1.05);
}

.menu-item h3 {
  font-size: 1.25rem;
  margin: 15px 20px 10px;
  color: #333;
  font-weight: 700;
  line-height: 1.3;
}

/* Fixed height description container */
.description-container {
  padding: 0 20px;
  min-height: 80px; /* Use min-height instead of fixed height */
  max-height: 100px; /* Add a max-height with overflow handling */
  overflow: hidden;
  margin-bottom: 10px;
  position: relative; /* For potential fade effect */
}

.description-container p {
  margin: 0;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 4; /* Increased from 3 to 4 lines */
  -webkit-box-orient: vertical;
  color: #666;
  font-size: 0.95rem;
  line-height: 1.5;
  word-wrap: break-word; /* Ensure long words break appropriately */
  hyphens: auto; /* Add hyphenation for better text wrapping */
}

/* Add fade effect at the bottom of long descriptions */
.description-container::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 20px;
  background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
  pointer-events: none;
}

/* Dark mode support for the fade effect */
:root.dark .description-container::after {
  background: linear-gradient(to bottom, rgba(45,45,45,0), rgba(45,45,45,1));
}

/* Updated footer styling with centered button */
.menu-item-footer {
  width: 100%;
  padding: 15px 20px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(249, 249, 249, 0.5);
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: auto;
  align-items: center; /* Center children horizontally */
  text-align: center; /* Center text content */
}

.menu-item .price {
  font-size: 1.4rem;
  font-weight: 700;
  color: #dd6013;
  margin: 0;
  position: relative;
  padding-bottom: 5px;
  display: inline-block; /* Allow the element to be centered */
  border-bottom: 3px solid #dd6013; /* Change from border-left to border-bottom for centered design */
}

/* Improved button styling */
.menu-item button {
  background: linear-gradient(135deg, #dd6013, #ffbd00);
  border: none;
  border-radius: 30px;
  padding: 12px 20px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(221, 96, 19, 0.2);
  width: auto; /* Allow button to size based on content */
  min-width: 80%; /* Set a minimum width */
  max-width: 90%; /* Set a maximum width */
  text-align: center;
  font-size: 1rem;
  margin: 0 auto; /* Center the button */
}

/* Dark mode compatibility */
:root.dark .menu-item {
  background-color: #2d2d2d;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
}

:root.dark .menu-item h3 {
  color: #f0f0f0;
}

:root.dark .description-container p {
  color: #bbb;
}

:root.dark .menu-item-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(45, 45, 45, 0.8);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .menu-item {
    border-radius: 12px;
  }
  
  .menu-item-image {
    height: 150px;
  }
  
  .menu-item h3 {
    font-size: 1.1rem;
    margin: 12px 15px 8px;
  }
  
  .description-container {
    height: 70px; /* Smaller fixed height for mobile */
    padding: 0 15px;
  }
  
  .menu-item-footer {
    padding: 12px 15px;
  }
  
  .menu-item .price {
    font-size: 1.2rem;
  }
  
  .menu-item button {
    padding: 8px 15px;
    font-size: 0.9rem;
  }
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
  background: white;
  padding: 0;
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: fit-content;
}

.cart-header {
  background: linear-gradient(135deg, #dd6013, #ffbd00);
  color: white;
  padding: 1.2rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.cart-count {
  background: white;
  color: #dd6013;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.empty-cart {
  padding: 3rem 1rem;
  text-align: center;
  color: #777;
}

.empty-cart-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.cart-content {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.cart-items {
  padding: 1rem;
  overflow-y: auto;
  max-height: 60vh;
}

.cart-item {
  background: #f9f9f9;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  transition: all 0.2s ease;
}

.cart-item:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
  transform: translateY(-2px);
}

.item-details {
  display: flex;
  justify-content: space-between;
}

.item-name-price {
  flex: 1;
}

.item-name-price h4 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
  color: #333;
}

.item-price {
  font-weight: 600;
  color: #dd6013;
}

.item-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-control {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 20px;
  padding: 0.3rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.quantity-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.quantity-btn:hover {
  background: #e0e0e0;
}

.quantity-value {
  padding: 0 0.8rem;
  font-weight: 600;
  min-width: 1.5rem;
  text-align: center;
}

.remove-btn {
  background: #f0f0f0;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-btn:hover {
  background: #ffecec;
}

.remove-btn .svg-icon {
  filter: brightness(0) saturate(100%) invert(30%) sepia(100%) saturate(1000%) hue-rotate(0deg);
}

.item-notes {
  width: 100%;
  margin-top: 0.8rem;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 40px;
  font-family: inherit;
}

.item-notes:focus {
  outline: none;
  border-color: #dd6013;
}

.cart-footer {
  padding: 1.2rem;
  background: white;
  border-top: 1px solid #eee;
  margin-top: auto;
}

.cart-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.total-label {
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
}

.total-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #dd6013;
}

.checkout-btn {
  width: 100%;
  padding: 1rem !important;
  border-radius: 12px !important;
  font-size: 1.2rem !important;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.5rem;
}

@media (max-width: 999px) {
  .cart-section {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding-top: 30px; /* Add padding at the top to make room for the button */
    box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
    transform: translateY(calc(100% - 60px));
    width: 100%;
    border-radius: 20px 20px 0 0;
  }

  .cart-section.expanded {
    transform: translateY(0);
  }

  .cart-header {
    cursor: pointer;
    padding: 1rem 1.5rem;
  }

  .cart-items {
    max-height: 50vh;
  }
  
  .toggle-cart {
    position: absolute;
    top: -20px; /* Adjusted from -25px to reduce how high it sits */
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 2px solid #eee; /* Slightly thicker border */
    border-radius: 50%;
    width: 50px; /* Increased from 30px */
    height: 50px; /* Increased from 30px */
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15); /* Added shadow for better visibility */
    z-index: 1001; /* Increase z-index to ensure it's always on top */
    padding: 0;
  }

  .toggle-icon {
    width: 20px;
    height: 20px;
    position: relative;
    transition: transform 0.3s ease;
  }

  .toggle-icon span {
    display: block;
    position: absolute;
    height: 3px;
    width: 100%;
    background: #dd6013;
    border-radius: 3px;
    opacity: 1;
    left: 0;
    transform: rotate(0deg);
    transition: all 0.3s ease;
  }

  .toggle-icon span:first-child {
    top: 8px;
  }

  .toggle-icon span:last-child {
    top: 16px;
  }

  .expanded .toggle-icon span:first-child {
    top: 12px;
    transform: rotate(45deg);
  }

  .expanded .toggle-icon span:last-child {
    top: 12px;
    transform: rotate(-45deg);
  }

  .checkout-btn {
    padding: 1.2rem !important;
  }

  /* Remove any overflow hidden properties that might clip the button */
  .cart-content, .cart-items {
    overflow: visible;
    overflow-y: auto;
  }
}

/* Dark mode compatibility */
:root.dark .cart-section {
  background-color: #2d2d2d;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

:root.dark .cart-item {
  background: #383838;
}

:root.dark .item-name-price h4 {
  color: #eee;
}

:root.dark .empty-cart {
  color: #aaa;
}

:root.dark .quantity-control {
  background: #2d2d2d;
}

:root.dark .quantity-btn,
:root.dark .remove-btn {
  background: #444;
}

:root.dark .quantity-btn:hover {
  background: #555;
}

:root.dark .remove-btn:hover {
  background: #633030;
}

:root.dark .item-notes {
  background: #383838;
  border-color: #444;
  color: #eee;
}

:root.dark .total-label {
  color: #eee;
}

:root.dark .cart-footer {
  background: #2d2d2d;
  border-top: 1px solid #444;
}

@media (max-width: 999px) {
  .cart-section {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
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
    top: -25px; /* Moved slightly higher to be more visible */
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 2px solid #eee; /* Slightly thicker border */
    border-radius: 50%;
    width: 50px; /* Increased from 30px */
    height: 50px; /* Increased from 30px */
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15); /* Added shadow for better visibility */
    z-index: 100; /* Ensure it's above other elements */
    padding: 0;
  }

  .toggle-icon {
    width: 20px;
    height: 20px;
    position: relative;
    transition: transform 0.3s ease;
  }

  .toggle-icon span {
    display: block;
    position: absolute;
    height: 3px;
    width: 100%;
    background: #dd6013;
    border-radius: 3px;
    opacity: 1;
    left: 0;
    transform: rotate(0deg);
    transition: all 0.3s ease;
  }

  .toggle-icon span:first-child {
    top: 8px;
  }

  .toggle-icon span:last-child {
    top: 16px;
  }

  .expanded .toggle-icon span:first-child {
    top: 12px;
    transform: rotate(45deg);
  }

  .expanded .toggle-icon span:last-child {
    top: 12px;
    transform: rotate(-45deg);
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
  background: #2d2d2d;
  border-color: #444;
}

:root.dark .toggle-icon span {
  background: #ffbd00;
}

/* Fixed toggle button styles */
@media (max-width: 999px) {
  /* Fix cart section to ensure button visibility */
  .cart-section {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 999;
    padding-top: 40px; /* Increased padding to make more room for the button */
    margin-top: 40px; /* Add margin to create space for the button */
    overflow: visible !important; /* Force visible overflow */
  }
  
  /* Style the button to ensure it's always visible */
  .toggle-cart {
    position: fixed; /* Change to fixed positioning */
    bottom: calc(100% - 25px); /* Position relative to viewport bottom */
    left: 50%;
    transform: translateX(-50%);
    z-index: 1999; /* Very high z-index */
    width: 60px; /* Slightly larger */
    height: 60px; /* Slightly larger */
    border: 3px solid #eee;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.2);
    pointer-events: auto !important; /* Ensure clickability */
    /* Add better center alignment */
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: visible;
    background: white;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  
  /* Add hover effect */
  .toggle-cart:hover {
    transform: translateX(-50%) scale(1.1);
  }
  
  /* Add active effect */
  .toggle-cart:active {
    transform: translateX(-50%) scale(0.95);
  }
  
  /* Improved toggle icon styling */
  .toggle-icon {
    width: 24px;
    height: 24px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    /* Center the icon properly */
    margin: 0 auto;
  }
  
  .toggle-icon span {
    position: absolute;
    height: 3px;
    width: 100%;
    background: #dd6013;
    border-radius: 3px;
    left: 0;
    transition: all 0.4s cubic-bezier(0.68, -0.6, 0.32, 1.6);
  }
  
  .toggle-icon span:first-child {
    top: 8px;
    transform-origin: center;
  }
  
  .toggle-icon span:last-child {
    top: 16px;
    transform-origin: center;
  }
  
  /* Fancy animation for expanded state */
  .expanded .toggle-icon span:first-child {
    top: 12px;
    transform: rotate(45deg) scaleX(1.2);
  }
  
  .expanded .toggle-icon span:last-child {
    top: 12px;
    transform: rotate(-45deg) scaleX(1.2);
  }
  
  /* Add a subtle pulse animation for the button when cart has items */
  @keyframes gentle-pulse {
    0% { box-shadow: 0 -4px 12px rgba(0,0,0,0.2); }
    50% { box-shadow: 0 -4px 18px rgba(221, 96, 19, 0.3); }
    100% { box-shadow: 0 -4px 12px rgba(0,0,0,0.2); }
  }
  
  .cart-section:has(.cart-count) .toggle-cart {
    animation: gentle-pulse 2s infinite ease-in-out;
  }
}

/* Dark mode adjustments */
:root.dark .toggle-cart {
  background: #2d2d2d;
  border-color: #444;
  animation: none;
}

:root.dark .toggle-icon span {
  background: #ffbd00;
}

:root.dark .cart-section:has(.cart-count) .toggle-cart {
  animation: gentle-pulse-dark 2s infinite ease-in-out;
}

@keyframes gentle-pulse-dark {
  0% { box-shadow: 0 -4px 12px rgba(0,0,0,0.4); }
    50% { box-shadow: 0 -4px 18px rgba(255, 189, 0, 0.3); }
    100% { box-shadow: 0 -4px 12px rgba(0,0,0,0.4); }
}

/* Styles for cart when footer is visible */
@media (max-width: 999px) {
  /* When footer is visible, minimize the cart */
  .cart-section.footer-visible:not(.expanded) {
    transform: translateY(calc(100% - 30px)); /* Show just a hint */
    opacity: 0.9;
    transition: all 0.4s ease;
  }
  
  /* Mini indicator for minimized cart */
  .cart-mini-indicator {
    position: absolute;
    top: 5px;
    left: 0;
    right: 0;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    color: #dd6013;
    font-weight: bold;
  }
  
  .mini-count {
    background: #dd6013;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
  }
  
  .mini-total {
    font-size: 1.1rem;
  }
  
  .mini-arrow {
    animation: bounce 1.5s infinite;
    font-size: 1.2rem;
  }
  
  @keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
  }
  
  /* When cart is expanded, show it completely regardless of footer */
  .cart-section.footer-visible.expanded {
    transform: translateY(0);
    opacity: 1;
  }
  
  /* Ensure footer buttons always stay on top */
  .footer .custom-button {
    position: relative;
    z-index: 2000;
  }
}

/* ...existing styles... */
</style>
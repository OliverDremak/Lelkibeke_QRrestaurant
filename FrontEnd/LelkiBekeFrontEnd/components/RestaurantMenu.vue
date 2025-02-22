<template>
  <div class="menu-container container">
    <div class="category-buttons">
      <button @click="filterByCategory('')" :class="{ active: selectedCategory === '' }">
        All
      </button>
      <button v-for="category in uniqueCategories" :key="category" @click="filterByCategory(category)"
        :class="{ active: selectedCategory === category }">
        {{ category }}
      </button>
    </div>

    <div class="main-content row">
      <!-- Menu Section -->
      <div class="menu-section " :class="menuClass">
        <h2>Main Courses</h2>
        <div class="menu-grid">
          <div v-for="item in filteredMainCourses" :key="item.id" class="menu-item">
            <h3>{{ item.name }} ({{ item.category_name }})</h3>
            <p>{{ item.description }}</p>
            <p class="price">{{ item.price }}ft</p>
            <ButtonComponet @click="addToCart(item)" text="Add to cart" />
          </div>
        </div>
      </div>

      <div class="cart-section col-xl-4 col-lg-6" :class="{ expanded: isCartExpanded }">
        <h2 class="heading orderheading text-center mb-3">Your Order</h2>
        <div v-if="cart.length === 0">Your cart is empty</div>
        <div v-else>
          <div v-for="(cartItem, index) in cart" :key="index" class="cart-item row">
            <span class="col-5 ">{{ cartItem.name }}</span>
            <span class="col-3">{{ (cartItem.price * cartItem.quantity).toFixed(2) }}ft</span>
            <div class="quantitybuttons col-2 text-center">
              <button @click="decreaseQuantity(index)"><img src="../public/svgs/minus.svg" alt="Trash" class="svg-icon"
                  width="24" height="24"></button>
              <span style="margin-top: 2px;">{{ cartItem.quantity }}</span>
              <button @click="increaseQuantity(index)"><img src="../public/svgs/plus.svg" alt="Trash" class="svg-icon"
                  width="24" height="24"></button>
            </div>
            <button @click="removeFromCart(index)" class="trash col-2 "><img src="../public/svgs/trash.svg" alt="Trash"
                class="svg-icon" width="24" height="24"></button>
          </div>
          <hr>
          <div class="total">
            <span class="heading">Total: {{ cartTotal }}ft</span>
            <ButtonComponet @click="handleCheckout" text="Checkout" class="totheright" />
          </div>
        </div>
        <button class="toggle-cart" @click="toggleCart">
          {{ isCartExpanded ? '▼' : '▲' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import ButtonComponet from './ButtonComponet.vue';
const data = ref([]);
try {
  const response = await fetch('http://localhost:8000/api/menu');
  const result = await response.json();
  data.value = result;
  console.log(data.value);
} catch (error) {
  console.error('Error fetching menu data:', error);
}

const mainCourses = ref(data);
const isWide = ref(false);
const menuClass = computed(() => ({
  "menu-section": true,
  "col-xl-8 col-lg-6 col-md-12": isWide.value,
  "col-12": !isWide.value,
}));
const handleResize = () => {
  isWide.value = window.innerWidth >= 1000;
};

onMounted(() => {
  handleResize();
  window.addEventListener("resize", handleResize);
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
    cart.value.push({ ...item, quantity: 1 });
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
  const orderData = {
    user_id: 2, // Replace with actual user ID
    table_id: props.tableId,
    total_price: cartTotal.value,
    order_items: cart.value.map(item => ({
      menu_item_id: item.id,
      quantity: item.quantity,
      notes: item.notes || '' // Add notes field to menu items (e.g. { name: 'Pizza', notes: 'No cheese'
    }))
  };

  try {
    const response = await fetch('http://localhost:8000/api/sendOrder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(orderData)
    });

    // Check if the response status is OK (status in the range 200-299)
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Error placing order');
    }

    const responseData = await response.json();
    if (responseData.order_id) {
      alert('Order placed successfully!');

      window.location.href = '/thankyou';
      window.localStorage.removeItem('cart');
    }
  } catch (error) {
    console.error('Checkout error:', error);
    alert('Error placing order: ' + error.message);
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

.quantitybuttons *,
.trash {
  border: none;
  background-color: transparent;
}

.quantitybuttons button {
  cursor: pointer;
}

.menu-container {
  padding: 20px;
}

.category-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  overflow-x: auto;
  white-space: nowrap;
}

.category-buttons button {
  padding: 10px 20px;
  cursor: pointer;
  border: none;
  background-color: #f0f0f0;
  border-radius: 5px;
  transition: background-color 0.3s;
  white-space: nowrap;
}

.category-buttons button.active {
  background-color: #007bff;
  color: white;
}

.category-buttons button:hover {
  background-color: #e0e0e0;
}

.main-content {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  width: 100%;
}

.menu-section {
  margin-bottom: 20px;
}

.menu-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

.menu-item {
  border: 1px solid #ddd;
  background-color: #ddd;
  padding: 20px;
  border-radius: 5px;
  width: calc(33.333% - 20px);
  box-sizing: border-box;
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
  border-radius: 15px;
  max-height: max-content;
  box-shadow: 9px 9px 12px #b2b2b3, inset 0 0 7px rgba(0, 0, 0, 0.3)
}

.cart-item {
  align-items: center;
  margin-bottom: 20px;
}

.total {
  font-weight: bold;
  margin-top: 10px;
  margin-left: 3px;
}

.toggle-cart {
  margin-top: 10px;
}

@media (max-width: 1000px) {
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
</style>
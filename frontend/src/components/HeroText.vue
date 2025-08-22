<script setup>
import { ref, onMounted } from 'vue'

// Text content arrays
const headers = [
  "Find Amazing Wild Alaskan Recipes",
  "Reel in the Catch of the Day",
  "From Ocean to Table",
  "Hook Your Next Favorite Dish",
  "Taste the Untamed North",
  "Adventures in Alaskan Cuisine",
  "Catch Something Delicious",
  "Pure Alaska, No Compromises",
  "Where Wild Meets Wonderful",
  "The Great Alaskan Cookbook"
]

const subheaders = [
  "Discover delicious recipes using our advanced search filters",
  "Fresh seafood recipes from Alaska's pristine waters",
  "Authentic flavors from the Last Frontier",
  "Explore recipes as wild as Alaska itself",
  "Where wilderness meets culinary excellence",
  "Bold flavors from America's final frontier",
  "Sustainably sourced, incredibly delicious",
  "Recipes as authentic as the midnight sun",
  "Transform fresh catches into memorable meals",
  "Your guide to glacier-fresh flavors"
]

// Current index
const currentIndex = ref(0)

// Change text every 7 seconds for comfortable reading
const changeText = () => {
  currentIndex.value = (currentIndex.value + 1) % headers.length
}

// Start cycling when component mounts
onMounted(() => {
  setInterval(changeText, 7000)
})
</script>

<template>
  <div class="hero-text-container">
    <!-- Main header with transition -->
    <transition
      mode="out-in"
      enter-active-class="transition-all duration-1000 ease-out"
      enter-from-class="opacity-0 translate-y-6"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-1000 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-6"
    >
      <h1
        :key="currentIndex"
        class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 drop-shadow-lg"
      >
        {{ headers[currentIndex] }}
      </h1>
    </transition>

    <!-- Subheader with transition -->
    <transition
      mode="out-in"
      enter-active-class="transition-all duration-1000 delay-200 ease-out"
      enter-from-class="opacity-0 translate-x-6"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition-all duration-1000 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 -translate-x-6"
    >
      <p
        :key="currentIndex + 'sub'"
        class="text-lg md:text-xl lg:text-2xl font-light text-white/90 drop-shadow-md"
      >
        {{ subheaders[currentIndex] }}
      </p>
    </transition>
  </div>
</template>


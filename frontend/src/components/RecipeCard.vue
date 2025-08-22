<template>
  <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 overflow-hidden group">
    <!-- Recipe Image -->
    <div class="relative h-48 bg-gradient-to-br from-alaskan-100 to-ocean-100 overflow-hidden">
      <img 
        v-if="recipe.image_url" 
        :src="recipe.image_url" 
        :alt="recipe.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <ChefHat :size="48" class="text-alaskan-400 opacity-50" />
      </div>
    </div>
    
    <!-- Recipe Header -->
    <div class="p-6 pb-4">
      <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-alaskan-600 transition-colors">
        {{ recipe.name }}
      </h3>
      <div class="flex flex-col space-y-2 text-sm text-gray-600">
        <span class="flex items-center">
          <ChefHat :size="16" class="mr-2" />
          {{ recipe.primary_author?.name || recipe.author_email || 'Unknown Chef' }}
        </span>
        <span class="flex items-center">
          <Calendar :size="16" class="mr-2" />
          {{ formatDate(recipe.created_at) }}
        </span>
      </div>
    </div>

    <!-- Recipe Description -->
    <div class="px-6 pb-4">
      <p class="text-gray-700 leading-relaxed">{{ truncateText(recipe.description, 120) }}</p>
    </div>

    <!-- Recipe Stats -->
    <div class="px-6 pb-4">
      <div class="flex justify-between items-center bg-gray-50 rounded-lg p-3">
        <div class="flex items-center text-sm font-medium text-gray-700">
          <Utensils :size="16" class="mr-2" />
          {{ recipe.ingredient_count }} ingredients
        </div>
        <div class="flex items-center text-sm font-medium text-gray-700">
          <ClipboardList :size="16" class="mr-2" />
          {{ recipe.step_count }} steps
        </div>
      </div>
    </div>

    <!-- Ingredients Preview -->
    <div class="px-6 pb-4">
      <h4 class="text-sm font-semibold text-gray-800 mb-3">Key Ingredients:</h4>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(ingredient, index) in recipe.ingredients.slice(0, 3)"
          :key="index"
          class="inline-block bg-alaskan-100 text-alaskan-800 text-xs font-medium px-3 py-1 rounded-full border border-alaskan-200"
        >
          {{ ingredient.name || ingredient }}
        </span>
        <span 
          v-if="recipe.ingredients.length > 3" 
          class="inline-block bg-driftwood-100 text-driftwood-600 text-xs font-medium px-3 py-1 rounded-full border border-driftwood-200"
        >
          +{{ recipe.ingredients.length - 3 }} more
        </span>
      </div>
    </div>

    <!-- Action Button -->
    <div class="px-6 pb-6 pt-2 border-t border-gray-100">
      <router-link
        :to="`/recipe/${recipe.slug}`"
        class="block w-full bg-gradient-to-r from-alaskan-500 to-ocean-600 hover:from-alaskan-600 hover:to-ocean-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-200 hover:shadow-lg focus:ring-2 focus:ring-alaskan-500 focus:ring-offset-2"
      >
        <span class="flex items-center justify-center gap-2">
          <Eye :size="16" />
          View Full Recipe
        </span>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { ChefHat, Calendar, Utensils, ClipboardList, Eye } from 'lucide-vue-next'

const props = defineProps({
  recipe: {
    type: Object,
    required: true
  }
})

// Handle image load errors
const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = '<div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-alaskan-100 to-ocean-100"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-alaskan-400 opacity-50"><path d="M17 11h1a3 3 0 0 1 0 6h-1"></path><path d="M9 12v6"></path><path d="M13 12v6"></path><path d="M14 7.86c.1-.3.3-.6.6-.7a9.04 9.04 0 0 1 2.81-.2c.34.03.68.12 1 .28.67.3.98.94 1.08 1.59a8.99 8.99 0 0 1-.01 3.18 2 2 0 0 1-1.33 1.59c-.34.1-.69.16-1.05.16"></path><path d="M7 7c0-.55.45-1 1-1h4c.55 0 1 .45 1 1v2a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V7z"></path><path d="M5 11h1a3 3 0 0 1 0 6H5"></path><path d="M21 12h1"></path></svg></div>'
}

// Utility functions
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const truncateText = (text, maxLength) => {
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength).trim() + '...'
}

// Computed properties for recipe data
const recipeTitle = computed(() => props.recipe.name)
const recipeDescription = computed(() => props.recipe.description)
</script>


<template>
  <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100 overflow-hidden group">
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
import { computed } from 'vue'
import { ChefHat, Calendar, Utensils, ClipboardList, Eye } from 'lucide-vue-next'

const props = defineProps({
  recipe: {
    type: Object,
    required: true
  }
})

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


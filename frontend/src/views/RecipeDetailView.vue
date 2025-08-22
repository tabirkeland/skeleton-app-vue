<template>
  <div class="min-h-screen">
    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white rounded-xl shadow-lg p-12 text-center">
        <div class="animate-spin w-12 h-12 border-4 border-alaskan-500 border-t-transparent rounded-full mx-auto mb-6"></div>
        <p class="text-lg text-gray-600">Loading recipe details...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
        <h2 class="text-2xl font-bold text-red-800 mb-4">❌ Recipe Not Found</h2>
        <p class="text-red-700 mb-6">{{ error.message }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button 
            @click="refetch" 
            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          >
            Try Again
          </button>
        </div>
      </div>
    </div>

    <!-- Recipe Content -->
    <div v-if="recipe && !loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Hero Header -->
      <div class="bg-gradient-to-r from-alaskan-500 to-ocean-600 text-white rounded-2xl p-8 md:p-12 mb-8 shadow-xl">
        <h1 class="text-3xl md:text-5xl font-bold mb-6">{{ recipe.name }}</h1>
        
        <!-- Recipe Meta -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
          <div v-if="recipe.primary_author" class="flex items-center space-x-2">
            <span class="text-lg">👨‍🍳</span>
            <div>
              <div class="text-sm opacity-90">Chef</div>
              <div class="font-semibold">{{ recipe.primary_author.name }}</div>
            </div>
          </div>
          <div v-if="recipe.prep_time" class="flex items-center space-x-2">
            <span class="text-lg">⏱️</span>
            <div>
              <div class="text-sm opacity-90">Prep</div>
              <div class="font-semibold">{{ recipe.prep_time }}m</div>
            </div>
          </div>
          <div v-if="recipe.cook_time" class="flex items-center space-x-2">
            <span class="text-lg">🔥</span>
            <div>
              <div class="text-sm opacity-90">Cook</div>
              <div class="font-semibold">{{ recipe.cook_time }}m</div>
            </div>
          </div>
          <div v-if="recipe.servings" class="flex items-center space-x-2">
            <span class="text-lg">🍽️</span>
            <div>
              <div class="text-sm opacity-90">Servings</div>
              <div class="font-semibold">{{ recipe.servings }}</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-lg">📅</span>
            <div>
              <div class="text-sm opacity-90">Created</div>
              <div class="font-semibold">{{ formatDate(recipe.created_at) }}</div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="flex gap-4">
          <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center border border-white border-opacity-10">
            <div class="text-2xl font-bold">{{ recipe.ingredient_count }}</div>
            <div class="text-sm opacity-90">Ingredients</div>
          </div>
          <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 text-center border border-white border-opacity-10">
            <div class="text-2xl font-bold">{{ recipe.step_count }}</div>
            <div class="text-sm opacity-90">Steps</div>
          </div>
        </div>
      </div>

      <!-- Content Sections -->
      <div class="space-y-8">
        <!-- Description -->
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
            <span class="mr-3">📖</span>
            Description
          </h2>
          <p class="text-gray-700 text-lg leading-relaxed">{{ recipe.description }}</p>
        </div>

        <!-- Ingredients -->
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="mr-3">🥄</span>
            Ingredients
          </h2>
          <div class="space-y-3">
            <div 
              v-for="(ingredient, index) in recipe.ingredients" 
              :key="ingredient.id || index"
              class="flex items-center space-x-4 p-4 bg-gray-50 hover:bg-alaskan-50 rounded-lg transition-colors group"
            >
              <div class="flex-shrink-0 w-8 h-8 bg-alaskan-500 group-hover:bg-alaskan-600 text-white rounded-full flex items-center justify-center font-semibold text-sm transition-colors">
                {{ index + 1 }}
              </div>
              <span class="text-gray-900 font-medium">{{ ingredient.formatted || formatIngredient(ingredient) }}</span>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="mr-3">📋</span>
            Instructions
          </h2>
          <div class="space-y-6">
            <div 
              v-for="step in recipe.steps" 
              :key="step.id"
              class="flex space-x-6"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-salmon-500 text-white rounded-full flex items-center justify-center font-bold text-lg">
                {{ step.order }}
              </div>
              <div class="flex-1 pt-1">
                <h4 v-if="step.title" class="text-lg font-semibold text-gray-900 mb-2">{{ step.title }}</h4>
                <p class="text-gray-700 leading-relaxed">{{ step.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@vue/apollo-composable'
import { GET_RECIPE } from '../graphql/queries'

const route = useRoute()

// Get recipe slug from route params
const slug = computed(() => route.params.slug)

// GraphQL query for single recipe
const { result, loading, error, refetch } = useQuery(
  GET_RECIPE,
  () => ({ slug: slug.value }),
  {
    errorPolicy: 'all'
  }
)

// Computed recipe data
const recipe = computed(() => result.value?.recipe)

// Utility functions
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatIngredient = (ingredient) => {
  if (!ingredient) return ''
  const parts = []
  if (ingredient.quantity && ingredient.quantity !== 1) {
    parts.push(ingredient.quantity)
  }
  if (ingredient.unit) {
    parts.push(ingredient.unit)
  }
  parts.push(ingredient.name)
  return parts.join(' ')
}
</script>


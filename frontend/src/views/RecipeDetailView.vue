<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
        <div class="animate-spin w-12 h-12 border-4 border-alaskan-500 border-t-transparent rounded-full mx-auto mb-6"></div>
        <p class="text-lg text-gray-600">Loading recipe details...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-red-50 border border-red-200 rounded-2xl shadow-xl p-8 text-center">
        <h2 class="flex items-center justify-center gap-2 text-2xl font-bold text-red-800 mb-4">
          <X :size="24" />
          Recipe Not Found
        </h2>
        <p class="text-red-700 mb-6">{{ error.message }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button 
            @click="refetch" 
            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200 focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          >
            Try Again
          </button>
        </div>
      </div>
    </div>

    <!-- Recipe Content -->
    <div v-if="recipe && !loading" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Hero Header -->
      <div class="relative text-white rounded-2xl overflow-hidden mb-8 shadow-2xl"
           :style="recipe.image_url ? '' : 'background: linear-gradient(135deg, #0891b2 0%, #0e7490 50%, #0369a1 100%)'">
        <!-- Background Image with Enhanced Overlay -->
        <div v-if="recipe.image_url" class="absolute inset-0">
          <img 
            :src="recipe.image_url" 
            :alt="recipe.name"
            class="w-full h-full object-cover"
            @error="handleImageError"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-black/20"></div>
        </div>
        
        <!-- Fallback Gradient Overlay for Better Depth -->
        <div v-else class="absolute inset-0 bg-black/20"></div>
        
        <!-- Content -->
        <div class="relative p-8 md:p-12 backdrop-blur-sm">
          <h1 class="text-3xl md:text-5xl font-bold mb-4 drop-shadow-lg">{{ recipe.name }}</h1>
          
          <!-- Stats moved here -->
          <div class="flex gap-6 mb-6">
            <div class="flex items-center gap-2">
              <Utensils :size="20" class="text-golden-300" />
              <span class="text-lg font-semibold">{{ recipe.ingredient_count }} Ingredients</span>
            </div>
            <div class="flex items-center gap-2">
              <ClipboardList :size="20" class="text-golden-300" />
              <span class="text-lg font-semibold">{{ recipe.step_count }} Steps</span>
            </div>
          </div>
        
        <!-- Recipe Meta -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div v-if="recipe.primary_author" class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-lg p-3 transition-all duration-200 hover:bg-white/20">
            <ChefHat :size="20" class="text-golden-300" />
            <div>
              <div class="text-sm opacity-90">Chef</div>
              <div class="font-semibold">{{ recipe.primary_author.name }}</div>
            </div>
          </div>
          <div v-if="recipe.prep_time" class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-lg p-3 transition-all duration-200 hover:bg-white/20">
            <Clock :size="20" class="text-golden-300" />
            <div>
              <div class="text-sm opacity-90">Prep</div>
              <div class="font-semibold">{{ recipe.prep_time }}m</div>
            </div>
          </div>
          <div v-if="recipe.cook_time" class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-lg p-3 transition-all duration-200 hover:bg-white/20">
            <Flame :size="20" class="text-salmon-400" />
            <div>
              <div class="text-sm opacity-90">Cook</div>
              <div class="font-semibold">{{ recipe.cook_time }}m</div>
            </div>
          </div>
          <div v-if="recipe.servings" class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-lg p-3 transition-all duration-200 hover:bg-white/20">
            <UtensilsCrossed :size="20" class="text-golden-300" />
            <div>
              <div class="text-sm opacity-90">Servings</div>
              <div class="font-semibold">{{ recipe.servings }}</div>
            </div>
          </div>
          <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-lg p-3 transition-all duration-200 hover:bg-white/20">
            <Calendar :size="20" class="text-golden-300" />
            <div>
              <div class="text-sm opacity-90">Created</div>
              <div class="font-semibold">{{ formatDate(recipe.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

      <!-- Content Sections -->
      <div class="space-y-8">
        <!-- Author Details Section -->
        <div v-if="recipe.primary_author" class="relative bg-gray-50 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 p-6 md:p-8"
             style="background: linear-gradient(135deg, rgba(249,250,251,0.98) 0%, rgba(243,244,246,0.98) 100%)">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
            <ChefHat :size="24" class="mr-3 text-salmon-600" />
            About the Chef
          </h2>
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-16 h-16 bg-salmon-100 rounded-full flex items-center justify-center">
              <User :size="32" class="text-salmon-600" />
            </div>
            <div class="flex-1">
              <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ recipe.primary_author.name }}</h3>
              <p class="text-gray-600 mb-2">{{ recipe.primary_author.email }}</p>
              <p v-if="recipe.primary_author.about" class="text-gray-700 leading-relaxed">{{ recipe.primary_author.about }}</p>
            </div>
          </div>
        </div>
        <!-- Description -->
        <div class="relative bg-gray-50 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 p-6 md:p-8"
             style="background: linear-gradient(135deg, rgba(249,250,251,0.98) 0%, rgba(243,244,246,0.98) 100%)">
          <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
            <BookOpen :size="24" class="mr-3 text-alaskan-600" />
            Description
          </h2>
          <p class="text-gray-700 text-lg leading-relaxed">{{ recipe.description }}</p>
        </div>

        <!-- Ingredients -->
        <div class="relative bg-gray-50 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden"
             style="background: linear-gradient(135deg, rgba(249,250,251,0.98) 0%, rgba(243,244,246,0.98) 100%)">
          <button 
            @click="toggleIngredients"
            class="w-full p-6 md:p-8 pb-4 md:pb-4 hover:bg-white/40 transition-all duration-200 focus:outline-none focus:bg-white/40"
          >
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <Utensils :size="24" class="mr-3 text-ocean-600" />
                Ingredients
              </h2>
              <div class="flex items-center gap-3">
                <span class="bg-ocean-100 text-ocean-700 px-3 py-1 rounded-full text-sm font-semibold">
                  {{ recipe.ingredient_count }} items
                </span>
                <ChevronDown v-if="!isIngredientsOpen" :size="24" class="text-gray-500 transition-transform duration-200" />
                <ChevronUp v-else :size="24" class="text-gray-500 transition-transform duration-200" />
              </div>
            </div>
          </button>
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-[2000px]"
            leave-from-class="opacity-100 max-h-[2000px]"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-show="isIngredientsOpen" class="px-6 md:px-8 pb-6 md:pb-8">
              <div class="space-y-3 pt-2">
                <div 
                  v-for="(ingredient, index) in recipe.ingredients" 
                  :key="ingredient.id || index"
                  class="flex items-center space-x-4 p-4 bg-white/60 hover:bg-golden-50 rounded-lg transition-all duration-200 group border border-transparent hover:border-golden-200 hover:shadow-md"
                >
                  <div class="flex-shrink-0 w-8 h-8 bg-alaskan-500 group-hover:bg-alaskan-600 text-white rounded-full flex items-center justify-center font-semibold text-sm transition-all duration-200 group-hover:scale-110">
                    {{ index + 1 }}
                  </div>
                  <span class="text-gray-900 font-medium">{{ ingredient.formatted || formatIngredient(ingredient) }}</span>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Instructions -->
        <div class="relative bg-gray-50 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 mb-12 overflow-hidden"
             style="background: linear-gradient(135deg, rgba(249,250,251,0.98) 0%, rgba(243,244,246,0.98) 100%)">
          <button 
            @click="toggleInstructions"
            class="w-full p-6 md:p-8 pb-4 md:pb-4 hover:bg-white/40 transition-all duration-200 focus:outline-none focus:bg-white/40"
          >
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <ClipboardList :size="24" class="mr-3 text-salmon-600" />
                Instructions
              </h2>
              <div class="flex items-center gap-3">
                <span class="bg-salmon-100 text-salmon-700 px-3 py-1 rounded-full text-sm font-semibold">
                  {{ recipe.step_count }} steps
                </span>
                <ChevronDown v-if="!isInstructionsOpen" :size="24" class="text-gray-500 transition-transform duration-200" />
                <ChevronUp v-else :size="24" class="text-gray-500 transition-transform duration-200" />
              </div>
            </div>
          </button>
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-[3000px]"
            leave-from-class="opacity-100 max-h-[3000px]"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-show="isInstructionsOpen" class="px-6 md:px-8 pb-6 md:pb-8">
              <div class="space-y-6 pt-2">
                <div 
                  v-for="step in recipe.steps" 
                  :key="step.id"
                  class="flex space-x-6 p-4 rounded-lg hover:bg-white/60 transition-all duration-200 group"
                >
                  <div class="flex-shrink-0 w-10 h-10 bg-salmon-500 group-hover:bg-salmon-600 text-white rounded-full flex items-center justify-center font-bold text-lg transition-all duration-200 group-hover:scale-110">
                    {{ step.order }}
                  </div>
                  <div class="flex-1 pt-1">
                    <h4 v-if="step.title" class="text-lg font-semibold text-gray-900 mb-2">{{ step.title }}</h4>
                    <p class="text-gray-700 leading-relaxed">{{ step.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useQuery } from '@vue/apollo-composable'
import { GET_RECIPE } from '../graphql/queries'
import { ChefHat, Clock, Flame, UtensilsCrossed, Calendar, BookOpen, Utensils, ClipboardList, X, ChevronDown, ChevronUp, User } from 'lucide-vue-next'

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

// Accordion state
const isIngredientsOpen = ref(true)
const isInstructionsOpen = ref(true)

// Toggle functions
const toggleIngredients = () => {
  isIngredientsOpen.value = !isIngredientsOpen.value
}

const toggleInstructions = () => {
  isInstructionsOpen.value = !isInstructionsOpen.value
}

// Scroll to top when component mounts or route changes
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    left: 0,
    behavior: 'instant' // Use 'instant' for immediate scroll on navigation
  })
}

// Scroll to top on mount
onMounted(() => {
  scrollToTop()
})

// Watch for route changes (if navigating between different recipes)
watch(() => route.params.slug, () => {
  scrollToTop()
  // Reset accordion states when navigating to a new recipe
  isIngredientsOpen.value = true
  isInstructionsOpen.value = true
})

// Handle image load errors
const handleImageError = (event) => {
  event.target.style.display = 'none'
}

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


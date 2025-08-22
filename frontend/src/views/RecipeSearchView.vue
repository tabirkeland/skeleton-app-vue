<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="relative text-white pb-16 pt-12 min-h-[60vh] bg-cover bg-center bg-no-repeat"
         style="background-image: url('/src/assets/hero-background.png')">
      <!-- Optional overlay for better text readability -->
      <div class="absolute inset-0 bg-black/20"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="flex justify-center">
          <img src="/src/assets/logo-text.png" alt="Wild Alaskan Recipes" class="h-40 md:h-56 lg:h-64 filter brightness-0 invert drop-shadow-lg">
        </div>
        <HeroText />
      </div>
    </div>

    <!-- Search Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
      <div class="relative bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-6 md:p-8"
           style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%)">
        <!-- Loading Overlay -->
        <div v-if="loading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-20 rounded-2xl">
          <div class="text-center">
            <Loader2 class="animate-spin text-alaskan-500 mx-auto mb-2" :size="32" />
            <p class="text-sm text-gray-600">Searching recipes...</p>
          </div>
        </div>
        
        <!-- Keyword Search (Always Visible) -->
        <div class="relative space-y-3 mb-8">
          <label for="keyword" class="flex items-center gap-2 text-lg font-semibold text-gray-800">
            <Search :size="20" class="text-alaskan-600" />
            Search Recipes
          </label>
          <div class="flex items-center">
            <input
              id="keyword"
              v-model="searchParams.keyword"
              type="text"
              autocomplete="off"
              placeholder="What would you like to cook today?"
              @keydown.enter="executeSearch"
              :disabled="loading"
              class="flex-1 h-14 px-6 text-lg border-2 border-gray-200 rounded-l-xl focus:outline-none focus:ring-0 focus:border-gray-300 transition-all duration-200 text-gray-900 placeholder-gray-500 shadow-sm hover:border-gray-300 disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed"
            >
            <button
              @click="executeSearch"
              :disabled="loading || !searchParams.keyword.trim()"
              class="h-14 px-4 bg-alaskan-500 hover:bg-alaskan-600 text-white border-2 border-l-0 border-alaskan-500 hover:border-alaskan-600 transition-all duration-200 disabled:bg-alaskan-500/50 disabled:border-alaskan-500/50 disabled:cursor-not-allowed disabled:hover:bg-alaskan-500/50"
              :aria-label="loading ? 'Searching...' : 'Search'"
            >
              <Loader2 v-if="loading" :size="20" class="animate-spin" />
              <Search v-else :size="20" />
            </button>
            <button
              @click="toggleFilterMenu"
              ref="filterMenuRef"
              class="relative h-14 px-4 bg-white hover:bg-gray-50 text-gray-700 rounded-r-xl border-2 border-l-0 border-gray-200 hover:border-gray-300 transition-all duration-200"
              :class="{ 'bg-gray-50 border-gray-300': showFilterMenu }"
              aria-label="Filter options"
            >
              <ListFilter :size="20" class="text-gray-600" />
              <span v-if="activeFilterCount > 0" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                {{ activeFilterCount }}
              </span>
            </button>
          </div>
          
          <!-- Filter Dropdown Menu -->
          <div v-if="showFilterMenu" class="absolute right-0 top-16 w-56 bg-white rounded-lg shadow-xl border border-gray-300 z-50 transition-all duration-200">
            <div class="py-2">
                <div class="px-4 py-2 text-sm font-semibold text-gray-800 border-b border-gray-200">Add Filter</div>
                <button
                  @click="selectFilterType('ingredient')"
                  class="w-full text-left px-4 py-3 hover:bg-alaskan-50 flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 font-medium transition-colors"
                >
                  <Utensils :size="16" class="text-ocean-600" />
                  <span>Ingredient</span>
                </button>
                <button
                  @click="selectFilterType('author')"
                  class="w-full text-left px-4 py-3 hover:bg-alaskan-50 flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 font-medium transition-colors"
                >
                  <User :size="16" class="text-salmon-600" />
                  <span>Author</span>
                </button>
                <div v-if="hasActiveFilters" class="border-t border-gray-200 mt-2 pt-2">
                  <button
                    @click="clearSearch"
                    class="w-full text-left px-4 py-3 hover:bg-red-50 text-red-600 hover:text-red-700 text-sm font-medium transition-colors"
                  >
                    Clear All Filters
                  </button>
                </div>
              </div>
            </div>
        </div>

        <!-- Applied Filters Panel -->
        <div v-if="hasActiveFilters" class="bg-gray-50 rounded-lg p-3 mt-3">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-700">Active Filters</h3>
            <button 
              @click="clearSearch" 
              class="text-sm text-salmon-600 hover:text-salmon-700 transition-colors"
            >
              Clear all
            </button>
          </div>
          <div class="flex flex-wrap gap-2">
            <!-- Ingredient Chips -->
            <div
              v-for="(ingredient, index) in searchParams.ingredients"
              :key="`ingredient-${index}`"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-golden-100 border border-golden-400 rounded-md text-sm transition-all duration-200 hover:bg-golden-200"
            >
              <Utensils :size="14" class="text-ocean-600" />
              <span class="text-driftwood-800 font-medium">{{ ingredient }}</span>
              <button
                @click="removeIngredient(index)"
                class="ml-1 text-driftwood-700 hover:text-red-600 font-bold text-lg leading-none transition-colors"
                :aria-label="`Remove ingredient: ${ingredient}`"
              >
                ×
              </button>
            </div>

            <!-- Author Chips -->
            <div
              v-for="(author, index) in searchParams.authors"
              :key="`author-${index}`"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-golden-100 border border-golden-400 rounded-md text-sm transition-all duration-200 hover:bg-golden-200"
            >
              <User :size="14" class="text-salmon-600" />
              <span class="text-driftwood-800 font-medium">{{ author }}</span>
              <button
                @click="removeAuthor(index)"
                class="ml-1 text-driftwood-700 hover:text-red-600 font-bold text-lg leading-none transition-colors"
                :aria-label="`Remove author: ${author}`"
              >
                ×
              </button>
            </div>
          </div>
        </div>

        <!-- New Filter Input (Slide-down) -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="transform -translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="transform translate-y-0 opacity-100"
          leave-to-class="transform -translate-y-2 opacity-0"
        >
          <div v-if="showIngredientInput" class="mt-4 p-4 bg-golden-50 border-2 border-golden-300 rounded-lg shadow-sm">
            <div class="flex items-center gap-3">
              <Utensils :size="18" class="text-ocean-600" />
              <input
                id="temp-ingredient"
                ref="ingredientInput"
                v-model="tempIngredient"
                type="text"
                autocomplete="off"
                placeholder="Add ingredient (e.g. chocolate, flour)"
                @keydown.enter="addIngredient"
                @keydown.escape="cancelIngredientInput"
                class="flex-1 px-3 py-2 border-2 border-golden-400 rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 transition-all duration-200 bg-white text-gray-900 placeholder-gray-500"
              >
              <button
                @click="addIngredient"
                class="px-4 py-2 bg-golden-500 hover:bg-golden-600 text-white rounded-md transition-all duration-200 font-medium text-sm"
              >
                Add
              </button>
              <button
                @click="cancelIngredientInput"
                class="p-2 text-driftwood-600 hover:text-driftwood-800 transition-colors"
                aria-label="Cancel"
              >
                <X :size="18" />
              </button>
            </div>
          </div>
        </Transition>
        
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="transform -translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="transform translate-y-0 opacity-100"
          leave-to-class="transform -translate-y-2 opacity-0"
        >
          <div v-if="showAuthorInput" class="mt-4 p-4 bg-golden-50 border-2 border-golden-300 rounded-lg shadow-sm">
            <div class="flex items-center gap-3">
              <User :size="18" class="text-salmon-600" />
              <input
                id="temp-author"
                ref="authorInput"
                v-model="tempAuthor"
                type="email"
                autocomplete="off"
                placeholder="Add author email"
                @keydown.enter="addAuthor"
                @keydown.escape="cancelAuthorInput"
                class="flex-1 px-3 py-2 border-2 border-golden-400 rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 transition-all duration-200 bg-white text-gray-900 placeholder-gray-500"
              >
              <button
                @click="addAuthor"
                class="px-4 py-2 bg-golden-500 hover:bg-golden-600 text-white rounded-md transition-all duration-200 font-medium text-sm"
              >
                Add
              </button>
              <button
                @click="cancelAuthorInput"
                class="p-2 text-driftwood-600 hover:text-driftwood-800 transition-colors"
                aria-label="Cancel"
              >
                <X :size="18" />
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !recipes.length" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="animate-spin w-8 h-8 border-4 border-alaskan-500 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="flex items-center justify-center gap-2 text-lg text-gray-600">
          <Search :size="20" />
          Searching recipes...
        </p>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <p class="flex items-center justify-center gap-2 text-red-800 mb-4">
          <X :size="20" />
          Error loading recipes: {{ error.message }}
        </p>
        <button
          @click="refetch"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Search Results -->
    <div v-if="recipes.length" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <!-- Results Count Display -->
      <div v-if="searchExecuted" class="mb-6">
        <p class="text-lg text-driftwood-800" v-html="formattedResultsHtml"></p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <RecipeCard
          v-for="recipe in recipes"
          :key="recipe.id"
          :recipe="recipe"
        />
      </div>

      <!-- Load More Button -->
      <div v-if="hasNextPage" class="text-center">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="px-6 py-3 bg-alaskan-500 hover:bg-alaskan-600 disabled:bg-driftwood-400 text-white font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-alaskan-500 focus:ring-offset-2 disabled:cursor-not-allowed"
        >
          <span v-if="loadingMore" class="flex items-center gap-2">
            <Loader2 :size="16" class="animate-spin" />
            Loading...
          </span>
          <span v-else class="flex items-center gap-2">
            <FileText :size="16" />
            Load More Recipes
          </span>
        </button>
      </div>
    </div>

    <!-- No Results -->
    <div v-if="!loading && !error && !recipes.length && searchExecuted" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="mb-4">
          <UtensilsCrossed :size="48" class="mx-auto text-gray-400" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No recipes found</h3>
        <p class="text-gray-600 mb-4">No recipes match your search criteria</p>
        <p class="text-gray-500">Try different keywords or clear your filters</p>
      </div>
    </div>

    <!-- Welcome State -->
    <div v-if="!hasSearched && !loading && !recipes.length" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="mb-4">
          <Rocket :size="48" class="mx-auto text-gray-400" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Ready to find amazing recipes?</h3>
        <p class="text-gray-600 mb-2">Start searching using the form above!</p>
        <p class="text-gray-500">Try searching for "chocolate", or by ingredient like "flour"</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useLazyQuery } from '@vue/apollo-composable'
import { SEARCH_RECIPES } from '../graphql/queries'
import RecipeCard from '../components/RecipeCard.vue'
import HeroText from '../components/HeroText.vue'
import { Search, Utensils, X, Loader2, FileText, UtensilsCrossed, Rocket, ListFilter, User } from 'lucide-vue-next'

// Reactive search parameters
const searchParams = reactive({
  keyword: '',
  ingredients: [],
  authors: [],
  page: 1,
  perPage: 8
})

// Temporary input states for adding new filters
const tempIngredient = ref('')
const tempAuthor = ref('')
const showIngredientInput = ref(false)
const showAuthorInput = ref(false)

// Component state
const recipes = ref([])
const totalRecipes = ref(0)
const currentPage = ref(1)
const hasNextPage = ref(false)
const hasSearched = ref(false)
const loadingMore = ref(false)
const isTyping = ref(false) // Track if user is typing

// Filter dropdown state
const showFilterMenu = ref(false)
const filterMenuRef = ref(null)
const selectedFilterType = ref(null)
const tempFilterValue = ref('')

// Execute search function - called on Enter key or button click from search input
const executeSearch = () => {
  // Only execute if not already loading
  if (!loading.value) {
    searchExecuted.value = true // Mark that a search has been executed
    lastSearchedKeyword.value = searchParams.keyword // Capture the keyword at search time
    performSearch(true)
  }
}

// Execute search when filters change (immediate execution)
const executeFilterSearch = () => {
  if (!loading.value) {
    searchExecuted.value = true // Mark that a search has been executed
    performSearch(true)
  }
}

// Get current variables for query - returns static object, not reactive
const getQueryVariables = () => ({
  author_email: searchParams.authors.length > 0 ? searchParams.authors.join(',') : null,
  keyword: searchParams.keyword || null,
  ingredient: searchParams.ingredients.length > 0 ? searchParams.ingredients.join(',') : null,
  first: searchParams.perPage,
  page: searchParams.page
})

// GraphQL lazy query - doesn't execute until called
// Don't pass variables here to prevent any reactive tracking
const { result, loading, error, load, refetch, fetchMore } = useLazyQuery(
  SEARCH_RECIPES,
  null,  // No variables passed here - we'll pass them when calling load/refetch
  {
    errorPolicy: 'all',
    fetchPolicy: 'cache-and-network',
    notifyOnNetworkStatusChange: true
  }
)

// Computed properties
const hasAnySearchParams = computed(() => {
  return searchParams.keyword || searchParams.ingredients.length > 0 || searchParams.authors.length > 0
})

const hasActiveFilters = computed(() => {
  return searchParams.ingredients.length > 0 || searchParams.authors.length > 0
})

const activeFilterCount = computed(() => {
  return searchParams.ingredients.length + searchParams.authors.length
})

// Separate tracking for whether a search has been executed
const searchExecuted = ref(false)
const lastSearchedKeyword = ref('')  // Store the keyword from the last executed search

const formattedResultsText = computed(() => {
  // Only show results text after search has been executed
  if (!searchExecuted.value || loading.value) return ''
  
  const parts = []
  if (lastSearchedKeyword.value) {
    parts.push(`"${lastSearchedKeyword.value}"`)
  }
  searchParams.ingredients.forEach(i => parts.push(`"${i}"`))
  searchParams.authors.forEach(a => parts.push(`"${a}"`))
  
  if (totalRecipes.value === 0) {
    return 'No recipes found'
  } else if (totalRecipes.value === 1) {
    return 'Found 1 recipe' + (parts.length ? ` for ${parts.join(' and ')}` : '')
  } else {
    const prefix = parts.length ? `Showing ${totalRecipes.value} results for ` : `Showing ${totalRecipes.value} recipes`
    return parts.length ? prefix + parts.join(' and ') : prefix
  }
})

const formattedResultsHtml = computed(() => {
  return formattedResultsText.value.replace(/"([^"]+)"/g, '<span class="font-medium">"$1"</span>')
})

// Watch for query results
const updateRecipesFromResult = (queryResult, append = false) => {
  if (queryResult?.recipes) {
    const data = queryResult.recipes
    if (append) {
      recipes.value.push(...data.data)
    } else {
      recipes.value = data.data
    }
    totalRecipes.value = data.paginatorInfo.total
    currentPage.value = data.paginatorInfo.currentPage
    hasNextPage.value = data.paginatorInfo.hasMorePages
    hasSearched.value = true
  }
}

// Watch result changes
watch(result, (newResult) => {
  if (newResult) {
    updateRecipesFromResult(newResult)
  }
}, { immediate: true })

// Perform search
const performSearch = (reset = true) => {
  if (reset) {
    searchParams.page = 1
  }

  if (hasAnySearchParams.value) {
    // Pass variables directly when calling load/refetch
    const variables = getQueryVariables()
    try {
      if (hasSearched.value) {
        refetch(variables)
      } else {
        recipes.value = []
        load(null, { variables })
      }
    } catch (err) {
      console.error('Error in performSearch:', err)
    }
  } else {
    recipes.value = []
    hasSearched.value = false
  }
}

// Load more results
const loadMore = async () => {
  if (!hasNextPage.value || loadingMore.value) return

  loadingMore.value = true
  try {
    const nextPage = currentPage.value + 1
    searchParams.page = nextPage  // Update page for getQueryVariables

    const { data } = await fetchMore({
      variables: getQueryVariables()
    })

    if (data?.recipes) {
      updateRecipesFromResult(data, true)
    }
  } catch (err) {
    console.error('Error loading more recipes:', err)
  } finally {
    loadingMore.value = false
  }
}

// Clear search
const clearSearch = () => {
  searchParams.keyword = ''
  searchParams.ingredients = []
  searchParams.authors = []
  searchParams.page = 1
  tempIngredient.value = ''
  tempAuthor.value = ''
  showIngredientInput.value = false
  showAuthorInput.value = false
  searchExecuted.value = false // Reset search execution flag
  lastSearchedKeyword.value = '' // Clear the last searched keyword
  
  // Reload recipes with empty search params (like page load)
  const variables = getQueryVariables()
  refetch(variables)
}

// Filter management methods
const addIngredient = () => {
  const ingredient = tempIngredient.value.trim()
  if (ingredient && !searchParams.ingredients.includes(ingredient)) {
    searchParams.ingredients.push(ingredient)
    executeFilterSearch()
  }
  tempIngredient.value = ''
  showIngredientInput.value = false
}

const removeIngredient = (index) => {
  searchParams.ingredients.splice(index, 1)
  executeFilterSearch()
}

const addAuthor = () => {
  const author = tempAuthor.value.trim()
  if (author && !searchParams.authors.includes(author)) {
    searchParams.authors.push(author)
    executeFilterSearch()
  }
  tempAuthor.value = ''
  showAuthorInput.value = false
}

const removeAuthor = (index) => {
  searchParams.authors.splice(index, 1)
  executeFilterSearch()
}

const cancelIngredientInput = () => {
  tempIngredient.value = ''
  showIngredientInput.value = false
}

const cancelAuthorInput = () => {
  tempAuthor.value = ''
  showAuthorInput.value = false
}

// Filter dropdown methods
const toggleFilterMenu = () => {
  showFilterMenu.value = !showFilterMenu.value
  if (!showFilterMenu.value) {
    selectedFilterType.value = null
    tempFilterValue.value = ''
  }
}

const selectFilterType = (type) => {
  selectedFilterType.value = type
  tempFilterValue.value = ''
  showFilterMenu.value = false
  if (type === 'ingredient') {
    showIngredientInput.value = true
    setTimeout(() => document.getElementById('temp-filter-input')?.focus(), 100)
  } else if (type === 'author') {
    showAuthorInput.value = true
    setTimeout(() => document.getElementById('temp-filter-input')?.focus(), 100)
  }
}

const addFilterFromDropdown = () => {
  const value = tempFilterValue.value.trim()
  if (!value) return
  
  if (selectedFilterType.value === 'ingredient' && !searchParams.ingredients.includes(value)) {
    searchParams.ingredients.push(value)
    executeFilterSearch()
  } else if (selectedFilterType.value === 'author' && !searchParams.authors.includes(value)) {
    searchParams.authors.push(value)
    executeFilterSearch()
  }
  
  tempFilterValue.value = ''
  selectedFilterType.value = null
}

const cancelFilterInput = () => {
  tempFilterValue.value = ''
  selectedFilterType.value = null
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (filterMenuRef.value && !filterMenuRef.value.contains(event.target)) {
    showFilterMenu.value = false
  }
}

// Component is ready
onMounted(() => {
  // Load initial recipes on page load with current variables
  const variables = getQueryVariables()
  load(null, { variables })
  
  // Add click outside listener for dropdown
  document.addEventListener('click', handleClickOutside)
})

// Cleanup
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

import { onUnmounted } from 'vue'
</script>

<style scoped>
.recipe-search {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.search-header {
  text-align: center;
  margin-bottom: 40px;
}

.search-header h1 {
  color: #2c3e50;
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.search-header p {
  color: #7f8c8d;
  font-size: 1.1rem;
}

.search-form {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 40px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  align-items: end;
}

.form-row:last-child {
  margin-bottom: 0;
}

.form-group {
  flex: 1;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e1e8ed;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.clear-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.clear-btn:hover {
  background: #c0392b;
  transform: translateY(-1px);
}

.loading, .error, .no-results, .welcome {
  text-align: center;
  padding: 60px 20px;
  font-size: 1.1rem;
  color: #7f8c8d;
}

.error {
  color: #e74c3c;
}

.retry-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
}

.results-header {
  margin-bottom: 30px;
}

.results-header h2 {
  color: #2c3e50;
  font-size: 1.8rem;
}

.recipe-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.load-more {
  text-align: center;
  padding: 20px;
}

.load-more-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 16px 32px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.load-more-btn:hover:not(:disabled) {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.load-more-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 0;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .recipe-grid {
    grid-template-columns: 1fr;
  }
}
</style>
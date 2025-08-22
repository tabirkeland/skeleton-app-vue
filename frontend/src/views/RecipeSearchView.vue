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
      <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-6 md:p-8"
           style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%)">
        <!-- Keyword Search (Always Visible) -->
        <div class="space-y-3 mb-8">
          <label for="keyword" class="flex items-center gap-2 text-lg font-semibold text-gray-800">
            <Search :size="20" class="text-alaskan-600" />
            Search Recipes
          </label>
          <div class="relative">
            <input
              id="keyword"
              v-model="searchParams.keyword"
              type="text"
              placeholder="What would you like to cook today?"
              @input="debouncedSearch"
              class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-alaskan-500/20 focus:border-alaskan-500 transition-all duration-200 text-gray-900 placeholder-gray-500 shadow-sm hover:border-gray-300"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-6">
              <Search :size="20" class="text-gray-400" />
            </div>
          </div>
        </div>

        <!-- Active Filter Badges -->
        <div v-if="hasActiveFilters" class="mb-8">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Active Filters</h3>
            <button
              @click="clearSearch"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200"
            >
              <X :size="14" />
              Clear All
            </button>
          </div>
          <div class="flex flex-wrap gap-3">
            <!-- Ingredient Badges -->
            <div
              v-for="(ingredient, index) in searchParams.ingredients"
              :key="`ingredient-${index}`"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-medium transition-all hover:from-blue-600 hover:to-blue-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              <Utensils :size="16" />
              <span>{{ ingredient }}</span>
              <button
                @click="removeIngredient(index)"
                class="hover:bg-white/20 rounded-full p-1 transition-colors"
                :aria-label="`Remove ingredient: ${ingredient}`"
              >
                <X :size="12" />
              </button>
            </div>
            
            <!-- Author Badges -->
            <div
              v-for="(author, index) in searchParams.authors"
              :key="`author-${index}`"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg text-sm font-medium transition-all hover:from-purple-600 hover:to-purple-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              <Mail :size="16" />
              <span>{{ author }}</span>
              <button
                @click="removeAuthor(index)"
                class="hover:bg-white/20 rounded-full p-1 transition-colors"
                :aria-label="`Remove author: ${author}`"
              >
                <X :size="12" />
              </button>
            </div>
          </div>
        </div>

        <!-- Dynamic Filter Inputs -->
        <div class="space-y-6 mb-8">
          <!-- Ingredient Input -->
          <div v-if="showIngredientInput" class="bg-blue-50/50 rounded-xl p-6 border-2 border-blue-200 transition-all duration-300 ease-in-out">
            <label for="temp-ingredient" class="flex items-center gap-2 text-base font-semibold text-blue-800 mb-3">
              <Utensils :size="18" />
              Add Ingredient
            </label>
            <div class="relative">
              <input
                id="temp-ingredient"
                ref="ingredientInput"
                v-model="tempIngredient"
                type="text"
                placeholder="e.g. chocolate, flour, eggs..."
                @keydown.enter="addIngredient"
                @blur="addIngredient"
                @keydown.escape="cancelIngredientInput"
                class="w-full px-5 py-3 border-2 border-blue-300 rounded-lg focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-900 placeholder-gray-500 bg-white shadow-sm"
              >
              <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                <span class="text-xs text-gray-500">Press Enter to add</span>
              </div>
            </div>
          </div>

          <!-- Author Input -->
          <div v-if="showAuthorInput" class="bg-purple-50/50 rounded-xl p-6 border-2 border-purple-200 transition-all duration-300 ease-in-out">
            <label for="temp-author" class="flex items-center gap-2 text-base font-semibold text-purple-800 mb-3">
              <Mail :size="18" />
              Add Author Email
            </label>
            <div class="relative">
              <input
                id="temp-author"
                ref="authorInput"
                v-model="tempAuthor"
                type="email"
                placeholder="chef@example.com"
                @keydown.enter="addAuthor"
                @blur="addAuthor"
                @keydown.escape="cancelAuthorInput"
                class="w-full px-5 py-3 border-2 border-purple-300 rounded-lg focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 text-gray-900 placeholder-gray-500 bg-white shadow-sm"
              >
              <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                <span class="text-xs text-gray-500">Press Enter to add</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Addition Section -->
        <div class="bg-gray-50/50 rounded-xl p-6 border border-gray-100">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">Add Filters</h3>
          <div class="flex flex-wrap gap-3">
            <button
              v-if="!showIngredientInput"
              @click="showIngredientInput = true; $nextTick(() => $refs.ingredientInput?.focus())"
              class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white border border-blue-300 rounded-xl transition-all duration-200 font-medium text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              <Plus :size="16" />
              Add Ingredient
            </button>
            
            <button
              v-if="!showAuthorInput"
              @click="showAuthorInput = true; $nextTick(() => $refs.authorInput?.focus())"
              class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white border border-purple-300 rounded-xl transition-all duration-200 font-medium text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              <Plus :size="16" />
              Add Author
            </button>
          </div>
        </div>
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
      <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center">
          Found {{ totalRecipes }} recipe{{ totalRecipes !== 1 ? 's' : '' }}
        </h2>
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
    <div v-if="!loading && !error && !recipes.length && hasSearched" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
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
    <div v-if="!hasSearched && !loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
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
import { Search, Utensils, Mail, X, Plus, Trash2, Loader2, FileText, UtensilsCrossed, Rocket } from 'lucide-vue-next'

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

// Debounce search function
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (!loading.value) {
      performSearch(true)
    }
  }, 1200)
}

// Reactive variables object
const queryVariables = computed(() => ({
  author_email: searchParams.authors.length > 0 ? searchParams.authors.join(',') : null,
  keyword: searchParams.keyword || null,
  ingredient: searchParams.ingredients.length > 0 ? searchParams.ingredients.join(',') : null,
  first: searchParams.perPage,
  page: searchParams.page
}))

// GraphQL lazy query - doesn't execute until called
const { result, loading, error, load, refetch, fetchMore } = useLazyQuery(
  SEARCH_RECIPES,
  queryVariables,
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
    recipes.value = []
  }

  if (hasAnySearchParams.value) {
    // Use load for first time or refetch for subsequent calls
    try {
      if (hasSearched.value) {
        refetch()
      } else {
        load()
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
    const variables = {
      author_email: searchParams.authors.length > 0 ? searchParams.authors.join(',') : null,
      keyword: searchParams.keyword || null,
      ingredient: searchParams.ingredients.length > 0 ? searchParams.ingredients.join(',') : null,
      first: searchParams.perPage,
      page: nextPage
    }

    const { data } = await fetchMore({
      variables
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
  recipes.value = []
  hasSearched.value = false
}

// Filter management methods
const addIngredient = () => {
  const ingredient = tempIngredient.value.trim()
  if (ingredient && !searchParams.ingredients.includes(ingredient)) {
    searchParams.ingredients.push(ingredient)
    debouncedSearch()
  }
  tempIngredient.value = ''
  showIngredientInput.value = false
}

const removeIngredient = (index) => {
  searchParams.ingredients.splice(index, 1)
  debouncedSearch()
}

const addAuthor = () => {
  const author = tempAuthor.value.trim()
  if (author && !searchParams.authors.includes(author)) {
    searchParams.authors.push(author)
    debouncedSearch()
  }
  tempAuthor.value = ''
  showAuthorInput.value = false
}

const removeAuthor = (index) => {
  searchParams.authors.splice(index, 1)
  debouncedSearch()
}

const cancelIngredientInput = () => {
  tempIngredient.value = ''
  showIngredientInput.value = false
}

const cancelAuthorInput = () => {
  tempAuthor.value = ''
  showAuthorInput.value = false
}

// Component is ready
onMounted(() => {
  // Load initial recipes on page load
  load()
})
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
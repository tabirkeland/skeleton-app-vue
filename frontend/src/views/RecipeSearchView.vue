<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-alaskan-500 to-ocean-600 text-white pb-16 pt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="flex justify-center mb-6">
          <img src="/src/assets/logo-text.png" alt="Wild Alaskan Recipes" class="h-24 md:h-32 filter brightness-0 invert">
        </div>
        <h1 class="text-3xl md:text-5xl font-bold mb-4">Find Amazing Wild Alaskan Recipes</h1>
        <p class="text-lg md:text-xl opacity-90">Discover delicious recipes using our advanced search filters</p>
      </div>
    </div>

    <!-- Search Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
      <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="space-y-2">
            <label for="keyword" class="block text-sm font-semibold text-gray-700">🔍 Keyword Search</label>
            <input
              id="keyword"
              v-model="searchParams.keyword"
              type="text"
              placeholder="Search recipe name or description..."
              @input="debouncedSearch"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-alaskan-500 focus:border-transparent transition-colors text-gray-900 placeholder-gray-500"
            >
          </div>
          <div class="space-y-2">
            <label for="ingredient" class="block text-sm font-semibold text-gray-700">🥄 Ingredient</label>
            <input
              id="ingredient"
              v-model="searchParams.ingredient"
              type="text"
              placeholder="e.g. chocolate, flour, eggs..."
              @input="debouncedSearch"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-alaskan-500 focus:border-transparent transition-colors text-gray-900 placeholder-gray-500"
            >
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="space-y-2">
            <label for="author-name" class="block text-sm font-semibold text-gray-700">👨‍🍳 Author Name</label>
            <input
              id="author-name"
              v-model="searchParams.author_name"
              type="text"
              placeholder="e.g. Gordon Ramsay"
              @input="debouncedSearch"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-alaskan-500 focus:border-transparent transition-colors text-gray-900 placeholder-gray-500"
            >
          </div>
          <div class="space-y-2">
            <label for="author" class="block text-sm font-semibold text-gray-700">📧 Author Email</label>
            <input
              id="author"
              v-model="searchParams.author_email"
              type="email"
              placeholder="chef@example.com"
              @input="debouncedSearch"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-alaskan-500 focus:border-transparent transition-colors text-gray-900 placeholder-gray-500"
            >
          </div>
        </div>

        <div class="flex justify-center space-x-4">
          <button
            @click="performSearch"
            :disabled="!hasAnySearchParams || loading"
            class="px-6 py-3 bg-alaskan-500 hover:bg-alaskan-600 disabled:bg-gray-300 disabled:text-gray-500 text-white font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-alaskan-500 focus:ring-offset-2 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center">
              <div class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></div>
              Searching...
            </span>
            <span v-else>🔍 Search Recipes</span>
          </button>
          <button
            @click="clearSearch"
            class="px-6 py-3 bg-driftwood-100 hover:bg-driftwood-200 text-driftwood-700 font-semibold rounded-lg transition-colors focus:ring-2 focus:ring-alaskan-500 focus:ring-offset-2"
          >
            🗑️ Clear All Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !recipes.length" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="animate-spin w-8 h-8 border-4 border-alaskan-500 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-lg text-gray-600">🔍 Searching recipes...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <p class="text-red-800 mb-4">❌ Error loading recipes: {{ error.message }}</p>
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
          {{ loadingMore ? '⏳ Loading...' : '📄 Load More Recipes' }}
        </button>
      </div>
    </div>

    <!-- No Results -->
    <div v-if="!loading && !error && !recipes.length && hasSearched" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="text-6xl mb-4">🍽️</div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No recipes found</h3>
        <p class="text-gray-600 mb-4">No recipes match your search criteria</p>
        <p class="text-gray-500">Try different keywords or clear your filters</p>
      </div>
    </div>

    <!-- Welcome State -->
    <div v-if="!hasSearched && !loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="text-6xl mb-4">🚀</div>
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

// Reactive search parameters
const searchParams = reactive({
  keyword: '',
  ingredient: '',
  author_name: '',
  author_email: '',
  page: 1,
  perPage: 12
})

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
  author_name: searchParams.author_name || null,
  author_email: searchParams.author_email || null,
  keyword: searchParams.keyword || null,
  ingredient: searchParams.ingredient || null,
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
  return searchParams.keyword || searchParams.ingredient || searchParams.author_name || searchParams.author_email
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
      author_name: searchParams.author_name || null,
      author_email: searchParams.author_email || null,
      keyword: searchParams.keyword || null,
      ingredient: searchParams.ingredient || null,
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
  searchParams.ingredient = ''
  searchParams.author_name = ''
  searchParams.author_email = ''
  searchParams.page = 1
  recipes.value = []
  hasSearched.value = false
}

// Component is ready
onMounted(() => {
  // Component mounted
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
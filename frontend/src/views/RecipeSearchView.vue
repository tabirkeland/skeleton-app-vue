<template>
  <div class="recipe-search">
    <div class="search-header">
      <h1>Recipe Search 3000</h1>
      <p>Find delicious recipes using advanced search filters</p>
    </div>

    <!-- Search Form -->
    <div class="search-form">
      <div class="form-row">
        <div class="form-group">
          <label for="keyword">Keyword</label>
          <input
            id="keyword"
            v-model="searchParams.keyword"
            type="text"
            placeholder="Search recipe name or description..."
            @input="debouncedSearch"
          >
        </div>
        <div class="form-group">
          <label for="ingredient">Ingredient</label>
          <input
            id="ingredient"
            v-model="searchParams.ingredient"
            type="text"
            placeholder="e.g. chocolate, flour, eggs..."
            @input="debouncedSearch"
          >
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="author">Author Email</label>
          <input
            id="author"
            v-model="searchParams.author_email"
            type="email"
            placeholder="chef@example.com"
            @input="debouncedSearch"
          >
        </div>
        <div class="form-group">
          <button @click="clearSearch" class="clear-btn">Clear All</button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !recipes.length" class="loading">
      <p>🔍 Searching recipes...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error">
      <p>❌ Error loading recipes: {{ error.message }}</p>
      <button @click="refetch" class="retry-btn">Try Again</button>
    </div>

    <!-- Search Results -->
    <div v-if="recipes.length" class="search-results">
      <div class="results-header">
        <h2>Found {{ totalRecipes }} recipe{{ totalRecipes !== 1 ? 's' : '' }}</h2>
      </div>
      
      <div class="recipe-grid">
        <RecipeCard 
          v-for="recipe in recipes" 
          :key="recipe.id" 
          :recipe="recipe"
        />
      </div>

      <!-- Load More Button -->
      <div v-if="hasNextPage" class="load-more">
        <button 
          @click="loadMore" 
          :disabled="loadingMore"
          class="load-more-btn"
        >
          {{ loadingMore ? '⏳ Loading...' : '📄 Load More Recipes' }}
        </button>
      </div>
    </div>

    <!-- No Results -->
    <div v-if="!loading && !error && !recipes.length && hasSearched" class="no-results">
      <p>🍽️ No recipes found matching your search criteria</p>
      <p>Try different keywords or clear your filters</p>
    </div>

    <!-- Welcome State -->
    <div v-if="!hasSearched && !loading" class="welcome">
      <p>🚀 Start searching for recipes using the form above!</p>
      <p>Try searching for "chocolate", or by ingredient like "flour"</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useQuery } from '@vue/apollo-composable'
import { SEARCH_RECIPES } from '../graphql/queries'
import RecipeCard from '../components/RecipeCard.vue'

// Reactive search parameters
const searchParams = reactive({
  keyword: '',
  ingredient: '',
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
    performSearch(true)
  }, 500)
}

// GraphQL query
const { result, loading, error, refetch, fetchMore } = useQuery(
  SEARCH_RECIPES,
  () => ({
    author_email: searchParams.author_email || null,
    keyword: searchParams.keyword || null,
    ingredient: searchParams.ingredient || null,
    first: searchParams.perPage,
    page: searchParams.page
  }),
  {
    enabled: false, // Don't auto-execute on mount
    errorPolicy: 'all',
    fetchPolicy: 'cache-and-network'
  }
)

// Computed properties
const hasAnySearchParams = computed(() => {
  return searchParams.keyword || searchParams.ingredient || searchParams.author_email
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
const unwatchResult = result => {
  if (result.value) {
    updateRecipesFromResult(result.value)
  }
}

// Perform search
const performSearch = (reset = true) => {
  if (reset) {
    searchParams.page = 1
    recipes.value = []
  }
  
  if (hasAnySearchParams.value) {
    refetch()
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
    const { data } = await fetchMore({
      variables: {
        input: {
          keyword: searchParams.keyword || null,
          ingredient: searchParams.ingredient || null,
          author_email: searchParams.author_email || null,
          page: nextPage,
          perPage: searchParams.perPage
        }
      }
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
  searchParams.author_email = ''
  searchParams.page = 1
  recipes.value = []
  hasSearched.value = false
}

// Watch result changes
onMounted(() => {
  if (result) {
    unwatchResult(result)
  }
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
<template>
  <div class="recipe-detail">
    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Loading recipe details...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error">
      <h2>❌ Recipe Not Found</h2>
      <p>{{ error.message }}</p>
      <div class="error-actions">
        <button @click="refetch" class="retry-btn">Try Again</button>
        <router-link to="/" class="back-btn">← Back to Search</router-link>
      </div>
    </div>

    <!-- Recipe Content -->
    <div v-if="recipe && !loading" class="recipe-content">
      <!-- Header -->
      <div class="recipe-header">
        <div class="breadcrumb">
          <router-link to="/" class="breadcrumb-link">🏠 Recipe Search</router-link>
          <span class="breadcrumb-separator">→</span>
          <span class="breadcrumb-current">{{ recipe.name }}</span>
        </div>
        
        <h1 class="recipe-title">{{ recipe.name }}</h1>
        
        <div class="recipe-meta">
          <div class="meta-item">
            <span class="meta-label">👨‍🍳 Chef:</span>
            <span class="meta-value">{{ recipe.author_email }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">📅 Created:</span>
            <span class="meta-value">{{ formatDate(recipe.created_at) }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">🔄 Updated:</span>
            <span class="meta-value">{{ formatDate(recipe.updated_at) }}</span>
          </div>
        </div>

        <div class="recipe-stats">
          <div class="stat-card">
            <div class="stat-number">{{ recipe.ingredient_count }}</div>
            <div class="stat-label">Ingredients</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ recipe.step_count }}</div>
            <div class="stat-label">Steps</div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="recipe-section">
        <h2 class="section-title">📖 Description</h2>
        <div class="recipe-description">
          <p>{{ recipe.description }}</p>
        </div>
      </div>

      <!-- Ingredients -->
      <div class="recipe-section">
        <h2 class="section-title">🥄 Ingredients</h2>
        <div class="ingredients-list">
          <div 
            v-for="(ingredient, index) in recipe.ingredients" 
            :key="index"
            class="ingredient-item"
          >
            <span class="ingredient-number">{{ index + 1 }}</span>
            <span class="ingredient-text">{{ ingredient }}</span>
          </div>
        </div>
      </div>

      <!-- Steps -->
      <div class="recipe-section">
        <h2 class="section-title">📋 Instructions</h2>
        <div class="steps-list">
          <div 
            v-for="(step, index) in recipe.steps" 
            :key="index"
            class="step-item"
          >
            <div class="step-number">{{ index + 1 }}</div>
            <div class="step-content">
              <p>{{ step }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="recipe-footer">
        <router-link to="/" class="back-to-search-btn">
          ← Back to Recipe Search
        </router-link>
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
</script>

<style scoped>
.recipe-detail {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  line-height: 1.6;
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #7f8c8d;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  text-align: center;
  padding: 60px 20px;
  color: #e74c3c;
}

.error h2 {
  margin-bottom: 16px;
}

.error-actions {
  margin-top: 24px;
  display: flex;
  gap: 16px;
  justify-content: center;
}

.retry-btn, .back-btn {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.retry-btn {
  background: #3498db;
  color: white;
  border: none;
  cursor: pointer;
}

.back-btn {
  background: #95a5a6;
  color: white;
}

.retry-btn:hover, .back-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.recipe-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.recipe-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 40px 40px 30px;
}

.breadcrumb {
  margin-bottom: 20px;
  font-size: 0.9rem;
  opacity: 0.9;
}

.breadcrumb-link {
  color: white;
  text-decoration: none;
  transition: opacity 0.3s ease;
}

.breadcrumb-link:hover {
  opacity: 0.8;
}

.breadcrumb-separator {
  margin: 0 8px;
  opacity: 0.7;
}

.breadcrumb-current {
  opacity: 0.9;
}

.recipe-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 20px 0;
  line-height: 1.2;
}

.recipe-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  margin-bottom: 30px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
}

.meta-label {
  opacity: 0.9;
}

.meta-value {
  font-weight: 600;
}

.recipe-stats {
  display: flex;
  gap: 20px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
}

.recipe-section {
  padding: 40px;
}

.recipe-section:not(:last-child) {
  border-bottom: 1px solid #ecf0f1;
}

.section-title {
  color: #2c3e50;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 24px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.recipe-description p {
  color: #5d6d7e;
  font-size: 1.1rem;
  line-height: 1.7;
  margin: 0;
}

.ingredients-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ingredient-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.ingredient-item:hover {
  background: #e9ecef;
  transform: translateX(4px);
}

.ingredient-number {
  background: #3498db;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.ingredient-text {
  color: #2c3e50;
  font-size: 1rem;
  font-weight: 500;
}

.steps-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.step-item {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.step-number {
  background: #e74c3c;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.step-content {
  flex: 1;
  padding-top: 8px;
}

.step-content p {
  color: #2c3e50;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0;
}

.recipe-footer {
  padding: 40px;
  text-align: center;
  background: #f8f9fa;
}

.back-to-search-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  text-decoration: none;
  padding: 16px 32px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.back-to-search-btn:hover {
  background: linear-gradient(135deg, #2980b9, #1f5582);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(52, 152, 219, 0.3);
}

/* Responsive design */
@media (max-width: 768px) {
  .recipe-detail {
    padding: 10px;
  }
  
  .recipe-header {
    padding: 30px 20px 20px;
  }
  
  .recipe-title {
    font-size: 2rem;
  }
  
  .recipe-meta {
    flex-direction: column;
    gap: 12px;
  }
  
  .recipe-stats {
    flex-direction: column;
  }
  
  .recipe-section {
    padding: 30px 20px;
  }
  
  .section-title {
    font-size: 1.3rem;
  }
  
  .step-item {
    gap: 16px;
  }
  
  .step-number {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }
}
</style>
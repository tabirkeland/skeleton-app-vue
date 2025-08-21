<template>
  <div class="recipe-card">
    <div class="recipe-header">
      <h3 class="recipe-title">{{ recipe.name }}</h3>
      <div class="recipe-meta">
        <span class="recipe-author">👨‍🍳 {{ recipe.author_email }}</span>
        <span class="recipe-date">📅 {{ formatDate(recipe.created_at) }}</span>
      </div>
    </div>

    <div class="recipe-description">
      <p>{{ truncateText(recipe.description, 120) }}</p>
    </div>

    <div class="recipe-stats">
      <div class="stat">
        <span class="stat-icon">🥄</span>
        <span class="stat-text">{{ recipe.ingredient_count }} ingredients</span>
      </div>
      <div class="stat">
        <span class="stat-icon">📋</span>
        <span class="stat-text">{{ recipe.step_count }} steps</span>
      </div>
    </div>

    <!-- Ingredients Preview -->
    <div class="ingredients-preview">
      <h4>Key Ingredients:</h4>
      <div class="ingredient-tags">
        <span
          v-for="(ingredient, index) in recipe.ingredients.slice(0, 3)"
          :key="index"
          class="ingredient-tag"
        >
          {{ ingredient }}
        </span>
        <span v-if="recipe.ingredients.length > 3" class="more-ingredients">
          +{{ recipe.ingredients.length - 3 }} more
        </span>
      </div>
    </div>

    <div class="recipe-actions">
      <router-link
        :to="`/recipe/${recipe.slug}`"
        class="view-recipe-btn"
      >
        👀 View Full Recipe
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

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

<style scoped>
.recipe-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 24px;
  transition: all 0.3s ease;
  border: 1px solid #e1e8ed;
  height: fit-content;
}

.recipe-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  border-color: #3498db;
}

.recipe-header {
  margin-bottom: 16px;
}

.recipe-title {
  color: #2c3e50;
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0 0 8px 0;
  line-height: 1.3;
}

.recipe-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.recipe-author,
.recipe-date {
  display: flex;
  align-items: center;
  gap: 4px;
}

.recipe-description {
  margin-bottom: 20px;
}

.recipe-description p {
  color: #5d6d7e;
  font-size: 0.95rem;
  line-height: 1.5;
  margin: 0;
}

.recipe-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #2c3e50;
  font-weight: 600;
  font-size: 0.9rem;
}

.stat-icon {
  font-size: 1.1rem;
}

.ingredients-preview {
  margin-bottom: 24px;
}

.ingredients-preview h4 {
  color: #2c3e50;
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 12px 0;
}

.ingredient-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.ingredient-tag {
  background: #e8f4fd;
  color: #2980b9;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 0.8rem;
  font-weight: 500;
  border: 1px solid #d4e9f7;
}

.more-ingredients {
  background: #ecf0f1;
  color: #7f8c8d;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 0.8rem;
  font-weight: 500;
  font-style: italic;
}

.recipe-actions {
  padding-top: 20px;
  border-top: 1px solid #ecf0f1;
}

.view-recipe-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  text-decoration: none;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  width: 100%;
  justify-content: center;
}

.view-recipe-btn:hover {
  background: linear-gradient(135deg, #2980b9, #1f5582);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.view-recipe-btn:active {
  transform: translateY(0);
}

/* Responsive design */
@media (max-width: 768px) {
  .recipe-card {
    padding: 20px;
  }

  .recipe-title {
    font-size: 1.25rem;
  }

  .recipe-stats {
    flex-direction: column;
    gap: 12px;
  }

  .ingredient-tags {
    gap: 6px;
  }

  .ingredient-tag,
  .more-ingredients {
    font-size: 0.75rem;
    padding: 4px 8px;
  }
}

/* Loading state */
.recipe-card.loading {
  opacity: 0.6;
  pointer-events: none;
}

.recipe-card.loading::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
</style>
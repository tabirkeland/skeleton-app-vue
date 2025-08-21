import { createRouter, createWebHistory } from 'vue-router'
import RecipeSearchView from '../views/RecipeSearchView.vue'
import RecipeDetailView from '../views/RecipeDetailView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'recipe-search',
      component: RecipeSearchView,
      meta: {
        title: 'Recipe Search 3000'
      }
    },
    {
      path: '/recipe/:slug',
      name: 'recipe-detail',
      component: RecipeDetailView,
      meta: {
        title: 'Recipe Details'
      }
    },
    // Legacy home route redirect
    {
      path: '/home',
      redirect: '/'
    }
  ]
})

export default router

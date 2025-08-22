<script setup>
import { RouterView } from 'vue-router'
import { useRoute } from 'vue-router'

const route = useRoute()
</script>

<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Minimal Navigation - only show on recipe detail pages -->
    <nav v-if="route.name === 'recipe-detail'" class="relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <router-link 
            to="/" 
            class="inline-flex items-center px-4 py-2 bg-white hover:bg-alaskan-50 text-driftwood-700 font-semibold rounded-lg shadow-sm border border-driftwood-200 transition-colors focus:ring-2 focus:ring-alaskan-500 focus:ring-offset-2"
          >
            ← Back to Search
          </router-link>
          <router-link 
            to="/" 
            class="flex items-center space-x-3 hover:opacity-80 transition-opacity"
          >
            <img src="/src/assets/logo-no-text.png" alt="Wild Alaskan" class="h-8 w-8">
            <span class="text-xl font-bold text-alaskan-700">Wild Alaskan Recipes</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Main content area - no constraints, let views control their layout -->
    <main>
      <RouterView />
    </main>
  </div>
</template>

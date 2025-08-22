<template>
  <div class="flex items-center gap-3">
    <!-- Per Page Selector -->
    <div class="flex items-center gap-2">
      <label for="per-page" class="text-sm text-driftwood-800">Show:</label>
      <select
        id="per-page"
        :value="perPage"
        @change="$emit('per-page-change', parseInt($event.target.value))"
        :disabled="disabled"
        class="px-3 py-1.5 text-sm text-driftwood-800 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-alaskan-500 focus:border-alaskan-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <option v-for="option in perPageOptions" :key="option" :value="option">
          {{ option }}
        </option>
      </select>
    </div>
    
    <!-- Pagination Controls -->
    <div class="flex items-center gap-1">
    <!-- First Page -->
    <button
      @click="$emit('page-change', 1)"
      :disabled="currentPage === 1 || disabled"
      class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
      aria-label="First page"
    >
      <ChevronsLeft :size="16" class="text-driftwood-800" />
    </button>
    
    <!-- Previous Page -->
    <button
      @click="$emit('page-change', currentPage - 1)"
      :disabled="currentPage === 1 || disabled"
      class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
      aria-label="Previous page"
    >
      <ChevronLeft :size="16" class="text-driftwood-800" />
    </button>
    
    <!-- Page Numbers -->
    <div class="flex items-center gap-1 px-2">
      <template v-for="page in paginationRange" :key="page">
        <button
          v-if="page !== '...'"
          @click="$emit('page-change', page)"
          :disabled="disabled"
          :class="[
            'min-w-[32px] h-8 px-2 rounded-lg font-medium text-sm transition-colors',
            page === currentPage 
              ? 'bg-alaskan-500 text-white hover:bg-alaskan-600' 
              : 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700'
          ]"
        >
          {{ page }}
        </button>
        <span v-else class="px-2 text-gray-400">...</span>
      </template>
    </div>
    
    <!-- Next Page -->
    <button
      @click="$emit('page-change', currentPage + 1)"
      :disabled="currentPage === totalPages || disabled"
      class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
      aria-label="Next page"
    >
      <ChevronRight :size="16" class="text-driftwood-800" />
    </button>
    
    <!-- Last Page -->
    <button
      @click="$emit('page-change', totalPages)"
      :disabled="currentPage === totalPages || disabled"
      class="p-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white transition-colors"
      aria-label="Last page"
    >
      <ChevronsRight :size="16" class="text-driftwood-800" />
    </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  },
  perPageOptions: {
    type: Array,
    default: () => [15, 25, 50, 100]
  },
  disabled: {
    type: Boolean,
    default: false
  },
  delta: {
    type: Number,
    default: 2
  }
})

defineEmits(['page-change', 'per-page-change'])

// Pagination range computation
const paginationRange = computed(() => {
  const delta = props.delta // Number of pages to show on each side of current page
  const range = []
  const rangeWithDots = []
  let l

  // Always show first page
  range.push(1)

  // Calculate range around current page
  for (let i = props.currentPage - delta; i <= props.currentPage + delta; i++) {
    if (i < props.totalPages && i > 1) {
      range.push(i)
    }
  }

  // Always show last page if there is more than one page
  if (props.totalPages > 1) {
    range.push(props.totalPages)
  }

  // Add dots where there are gaps
  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1)
      } else if (i - l !== 1) {
        rangeWithDots.push('...')
      }
    }
    rangeWithDots.push(i)
    l = i
  })

  return rangeWithDots
})
</script>
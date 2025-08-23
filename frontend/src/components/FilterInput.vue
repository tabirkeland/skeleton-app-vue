<template>
  <Transition
    enter-active-class="transition-all duration-200 ease-out"
    enter-from-class="transform -translate-y-2 opacity-0"
    enter-to-class="transform translate-y-0 opacity-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="transform translate-y-0 opacity-100"
    leave-to-class="transform -translate-y-2 opacity-0"
  >
    <div v-if="visible" class="mt-3 p-3 bg-golden-50 border-2 border-golden-300 rounded-lg shadow-sm">
      <div class="flex items-center gap-2">
        <component :is="icon" :size="16" :class="iconClass" />
        <input
          :id="inputId"
          ref="filterInput"
          v-model="internalValue"
          :type="inputType"
          autocomplete="off"
          :placeholder="placeholder"
          @keydown.enter="handleAdd"
          @keydown.escape="handleCancel"
          class="flex-1 px-3 py-1.5 border-2 border-golden-400 rounded-md focus:outline-none focus:ring-0 focus:border-gray-300 transition-all duration-200 bg-white text-gray-900 placeholder-gray-500 text-sm"
        >
        <button
          @click="handleAdd"
          class="px-3 py-1.5 bg-golden-500 hover:bg-golden-600 text-white rounded-md transition-all duration-200 font-medium text-sm"
        >
          Add
        </button>
        <button
          @click="handleCancel"
          class="p-1.5 text-driftwood-600 hover:text-driftwood-800 transition-colors"
          aria-label="Cancel"
        >
          <X :size="16" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    required: true
  },
  inputType: {
    type: String,
    default: 'text'
  },
  inputId: {
    type: String,
    required: true
  },
  icon: {
    type: [Object, Function],
    required: true
  },
  iconClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'add', 'cancel'])

const filterInput = ref(null)
const internalValue = ref(props.modelValue)

watch(() => props.modelValue, (newValue) => {
  internalValue.value = newValue
})

watch(internalValue, (newValue) => {
  emit('update:modelValue', newValue)
})

watch(() => props.visible, async (isVisible) => {
  if (isVisible) {
    await nextTick()
    filterInput.value?.focus()
  }
})

const handleAdd = () => {
  const value = internalValue.value.trim()
  if (value) {
    emit('add', value)
  }
}

const handleCancel = () => {
  emit('cancel')
}
</script>
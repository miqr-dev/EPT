<template>
  <div
    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium shadow-sm"
    :class="tone === 'success'
      ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-100'
      : 'bg-amber-50 text-amber-800 ring-1 ring-amber-100'"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{ tone?: 'success' | 'warning' }>(), {
  tone: 'success',
})
</script>

<script setup lang="ts">
import { SelectValue, type SelectValueProps } from 'radix-vue'
import { cn } from '@/lib/utils'

const props = defineProps<SelectValueProps & { class?: string }>()
</script>

<template>
  <SelectValue
    :class="cn('text-sm', props.class)"
    v-bind="props"
  />
</template>

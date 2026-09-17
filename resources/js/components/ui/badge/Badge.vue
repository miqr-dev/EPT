<script setup lang="ts">
import { cn } from '@/lib/utils'
import { badgeVariants, type BadgeVariants } from './badgeVariants'

const { variant = 'default' } = defineProps<{
  variant?: BadgeVariants['variant']
}>()
</script>

<template>
  <div :class="cn(badgeVariants({ variant }), $attrs.class ?? '')">
    <slot />
  </div>
</template>

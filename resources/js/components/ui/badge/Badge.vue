<script setup lang="ts">
import { cn } from '@/lib/utils'
import { badgeVariants, type BadgeVariants } from './badgeVariants'

const { variant } = withDefaults(defineProps<{
  variant?: BadgeVariants['variant']
}>(), { variant: 'default' })
</script>

<template>
  <div :class="cn(badgeVariants({ variant }), $attrs.class ?? '')">
    <slot />
  </div>
</template>
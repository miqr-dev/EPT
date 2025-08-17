<script setup lang="ts">
import { cn } from '@/lib/utils'
import { badgeVariants, type BadgeVariants } from './badgeVariants'

interface Props {
  variant?: BadgeVariants['variant']
}
const { variant = 'default' } = defineProps<Props>()
</script>

<template>
  <div :class="cn(badgeVariants({ variant }), $attrs.class ?? '')">
    <slot />
  </div>
</template>
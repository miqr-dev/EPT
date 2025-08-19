<script setup lang="ts">
import { cn } from '@/lib/utils'
import { DialogTitle, type DialogTitleProps, useForwardProps } from 'reka-ui'
import { type HTMLAttributes } from 'vue'

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>()

// Forward props directly to the underlying primitive. Using the reactive `props`
// object avoids calling `toRefs` on a plain object, which triggered console
// warnings when this component rendered.
const forwardedProps = useForwardProps(props)
</script>

<template>
  <DialogTitle
    data-slot="dialog-title"
    v-bind="forwardedProps"
    :class="cn('text-lg leading-none font-semibold', props.class)"
  >
    <slot />
  </DialogTitle>
</template>

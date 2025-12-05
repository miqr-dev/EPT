<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const props = defineProps<{
  svg: string;
  selection: number | null;
  disabled?: boolean;
}>();

const emit = defineEmits(['update:selection']);

const svgContainer = ref<HTMLDivElement | null>(null);

function updatePathStyles() {
  if (!svgContainer.value) return;
  const paths = svgContainer.value.querySelectorAll('path');
  paths.forEach((path, index) => {
    if (index === props.selection) {
      path.setAttribute('fill', 'red');
    } else {
      // Revert to original color, assuming it's black
      path.setAttribute('fill', '#050709');
    }
  });
}

function handlePathClick(event: Event) {
  if (props.disabled) return;
  const target = event.target as SVGPathElement;
  if (target.tagName !== 'path') return;

  if (!svgContainer.value) return;
  const paths = Array.from(svgContainer.value.querySelectorAll('path'));
  const index = paths.indexOf(target);

  if (index !== -1) {
    emit('update:selection', index);
  }
}

onMounted(() => {
  updatePathStyles();
  if (svgContainer.value) {
    svgContainer.value.addEventListener('click', handlePathClick);
  }
});

watch(() => props.selection, () => {
  updatePathStyles();
});

watch(() => props.svg, () => {
    onMounted(() => {
        updatePathStyles();
    });
});
</script>

<template>
  <div ref="svgContainer" v-html="svg" />
</template>

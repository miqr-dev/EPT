<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from 'vue';

const props = defineProps<{
  svg: string;
  selection: number | null;
  disabled?: boolean;
}>();

const emit = defineEmits(['update:selection']);

const svgContainer = ref<HTMLDivElement | null>(null);

// Store original colors to revert to
const originalColors = ref<string[]>([]);

// This function updates path colors based on the current selection.
const updatePathColors = () => {
  if (!svgContainer.value) return;
  const paths = svgContainer.value.querySelectorAll('path');
  paths.forEach((path, index) => {
    if (index === props.selection) {
      path.setAttribute('fill', 'red');
    } else {
      // Restore the original color
      path.setAttribute('fill', originalColors.value[index] || 'black');
    }
  });
};


// This function runs when the component is mounted and whenever the svg prop changes.
// It sets up the SVG content, styles, and event listeners.
const setupSvg = () => {
  if (!svgContainer.value) return;

  svgContainer.value.innerHTML = props.svg;
  const paths = svgContainer.value.querySelectorAll('path');

  originalColors.value = [];
  paths.forEach((path) => {
    // Store the original fill color for later use
    originalColors.value.push(path.getAttribute('fill') || 'black');

    // This is the key fix: ensures the entire fill area of the path is clickable
    path.style.pointerEvents = 'all';
  });

  // Apply initial selection style
  updatePathColors();
};

// Event handler for clicks within the SVG container.
function handleSvgClick(event: Event) {
  if (props.disabled) return;

  const target = event.target as HTMLElement;
  const paths = svgContainer.value?.querySelectorAll('path');
  if (target.tagName.toLowerCase() === 'path' && paths) {
    const index = Array.from(paths).indexOf(target as SVGPathElement);
    emit('update:selection', index === props.selection ? null : index);
  }
}

// Initial setup
onMounted(() => {
    setupSvg();
});

// Re-setup if the SVG content changes
watch(() => props.svg, () => {
    // nextTick ensures the container is available after a potential v-if
    nextTick(() => {
      setupSvg();
    });
});

// Update colors when selection prop changes
watch(() => props.selection, () => {
    updatePathColors();
});

</script>

<template>
  <div ref="svgContainer" @click="handleSvgClick" />
</template>

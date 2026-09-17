<script setup lang="ts">
withDefaults(
    defineProps<{
        checked: boolean;
        editable?: boolean;
        manual?: boolean;
        label?: string;
    }>(),
    {
        editable: false,
        manual: false,
        label: 'Markierung umschalten',
    },
);

const emit = defineEmits<{
    toggle: [];
}>();
</script>

<template>
    <button
        v-if="editable"
        type="button"
        class="entrance-analysis-mark"
        :class="{ 'entrance-analysis-mark--checked': checked, 'entrance-analysis-mark--manual': manual }"
        :aria-label="label"
        :aria-pressed="checked"
        @click="emit('toggle')"
    >
        <span aria-hidden="true">{{ checked ? 'X' : '' }}</span>
    </button>
    <span
        v-else
        class="entrance-analysis-mark entrance-analysis-mark--static"
        :class="{ 'entrance-analysis-mark--checked': checked, 'entrance-analysis-mark--manual': manual }"
    >
        {{ checked ? 'X' : '' }}
    </span>
</template>

<style scoped>
.entrance-analysis-mark {
    display: flex;
    width: 100%;
    min-height: 4mm;
    align-items: center;
    justify-content: center;
    border: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    line-height: 1;
    padding: 0;
}

button.entrance-analysis-mark {
    cursor: pointer;
}

button.entrance-analysis-mark:hover {
    background: #eff6ff;
}

button.entrance-analysis-mark:focus-visible {
    outline: 1px solid #2563eb;
    outline-offset: -1px;
}

.entrance-analysis-mark--manual {
    color: #0f766e;
}

.entrance-analysis-mark--static {
    cursor: default;
}

@media print {
    .entrance-analysis-mark--manual {
        color: inherit;
    }
}
</style>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Bold, Italic, List, ListOrdered, Palette, RemoveFormatting, Underline } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        placeholder?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Text schreiben...',
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const editor = ref<HTMLElement | null>(null);
const selectedColor = ref('#334155');
const selectedSize = ref('16px');

let savedRange: Range | null = null;

const fontSizeCommands: Record<string, string> = {
    '14px': '2',
    '16px': '3',
    '18px': '4',
    '20px': '5',
    '24px': '6',
};

const fontSizeLabels = [
    { value: '14px', label: '14 px' },
    { value: '16px', label: '16 px' },
    { value: '18px', label: '18 px' },
    { value: '20px', label: '20 px' },
    { value: '24px', label: '24 px' },
];

const fontTagSizeMap: Record<string, string> = {
    '1': '12px',
    '2': '14px',
    '3': '16px',
    '4': '18px',
    '5': '20px',
    '6': '24px',
    '7': '28px',
};

watch(
    () => props.modelValue,
    (value) => {
        const nextValue = value ?? '';

        if (editor.value && editor.value.innerHTML !== nextValue) {
            editor.value.innerHTML = nextValue;
        }
    },
);

onMounted(() => {
    if (editor.value) {
        editor.value.innerHTML = props.modelValue ?? '';
    }

    document.addEventListener('selectionchange', saveSelection);
});

onBeforeUnmount(() => {
    document.removeEventListener('selectionchange', saveSelection);
});

function isBlankHtml(value: string): boolean {
    return (
        value
            .replace(/<br\s*\/?>/gi, '')
            .replace(/&nbsp;/gi, ' ')
            .replace(/<[^>]*>/g, '')
            .trim() === ''
    );
}

function saveSelection() {
    const selection = window.getSelection();

    if (!selection || selection.rangeCount === 0 || !editor.value) {
        return;
    }

    const anchorNode = selection.anchorNode;

    if (anchorNode && editor.value.contains(anchorNode)) {
        savedRange = selection.getRangeAt(0).cloneRange();
    }
}

function restoreSelection() {
    if (!savedRange) {
        return;
    }

    const selection = window.getSelection();

    if (!selection) {
        return;
    }

    selection.removeAllRanges();
    selection.addRange(savedRange);
}

function normalizeFontTags() {
    if (!editor.value) {
        return;
    }

    editor.value.querySelectorAll('font').forEach((font) => {
        const span = document.createElement('span');
        const color = font.getAttribute('color');
        const size = font.getAttribute('size');

        if (color) {
            span.style.color = color;
        }

        if (size && fontTagSizeMap[size]) {
            span.style.fontSize = fontTagSizeMap[size];
        }

        while (font.firstChild) {
            span.appendChild(font.firstChild);
        }

        font.replaceWith(span);
    });
}

function syncContent() {
    if (!editor.value) {
        return;
    }

    normalizeFontTags();

    const html = editor.value.innerHTML;

    emit('update:modelValue', isBlankHtml(html) ? '' : html);
}

function runCommand(command: string, value?: string) {
    editor.value?.focus();
    restoreSelection();
    document.execCommand(command, false, value);
    syncContent();

    void nextTick(() => {
        saveSelection();
    });
}

function applySize(value: string | number | undefined) {
    if (!value) {
        return;
    }

    selectedSize.value = String(value);
    runCommand('fontSize', fontSizeCommands[selectedSize.value] ?? '3');
}

function applyColor(event: Event) {
    const target = event.target as HTMLInputElement;

    selectedColor.value = target.value;
    runCommand('foreColor', selectedColor.value);
}

function pastePlainText(event: ClipboardEvent) {
    event.preventDefault();

    const text = event.clipboardData?.getData('text/plain') ?? '';
    document.execCommand('insertText', false, text);
    syncContent();
}
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-950">
        <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 p-2 dark:border-slate-700">
            <Button type="button" size="icon" variant="ghost" title="Fett" @mousedown.prevent @click="runCommand('bold')">
                <Bold class="h-4 w-4" />
            </Button>
            <Button type="button" size="icon" variant="ghost" title="Kursiv" @mousedown.prevent @click="runCommand('italic')">
                <Italic class="h-4 w-4" />
            </Button>
            <Button type="button" size="icon" variant="ghost" title="Unterstrichen" @mousedown.prevent @click="runCommand('underline')">
                <Underline class="h-4 w-4" />
            </Button>
            <span class="mx-1 h-6 w-px bg-slate-200 dark:bg-slate-700" />
            <Button type="button" size="icon" variant="ghost" title="Aufzaehlung" @mousedown.prevent @click="runCommand('insertUnorderedList')">
                <List class="h-4 w-4" />
            </Button>
            <Button type="button" size="icon" variant="ghost" title="Nummerierte Liste" @mousedown.prevent @click="runCommand('insertOrderedList')">
                <ListOrdered class="h-4 w-4" />
            </Button>
            <span class="mx-1 h-6 w-px bg-slate-200 dark:bg-slate-700" />
            <label
                class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md hover:bg-slate-100 dark:hover:bg-slate-800"
                title="Textfarbe"
                @mousedown="saveSelection"
            >
                <Palette class="h-4 w-4" />
                <input class="sr-only" type="color" :value="selectedColor" @input="applyColor" />
            </label>
            <Select :model-value="selectedSize" @update:model-value="applySize">
                <SelectTrigger class="h-9 w-[92px]">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="size in fontSizeLabels" :key="size.value" :value="size.value">
                        {{ size.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Button type="button" size="icon" variant="ghost" title="Formatierung entfernen" @mousedown.prevent @click="runCommand('removeFormat')">
                <RemoveFormatting class="h-4 w-4" />
            </Button>
        </div>
        <div
            ref="editor"
            class="editor-surface min-h-44 w-full overflow-y-auto px-4 py-3 text-sm leading-7 text-slate-900 outline-none dark:text-slate-100"
            contenteditable="true"
            :data-placeholder="placeholder"
            @blur="syncContent"
            @input="syncContent"
            @paste="pastePlainText"
        />
    </div>
</template>

<style scoped>
.editor-surface:empty::before {
    color: rgb(148 163 184);
    content: attr(data-placeholder);
    pointer-events: none;
}

.editor-surface :deep(ul) {
    list-style: disc;
    margin: 0.5rem 0 0.5rem 1.25rem;
}

.editor-surface :deep(ol) {
    list-style: decimal;
    margin: 0.5rem 0 0.5rem 1.25rem;
}

.editor-surface :deep(p),
.editor-surface :deep(div) {
    margin: 0.35rem 0;
}
</style>

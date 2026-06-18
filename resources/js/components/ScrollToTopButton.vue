<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { usePage } from '@inertiajs/vue3';
import { ArrowUp } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

type ScrollTarget = HTMLElement | Window;

const SCROLL_THRESHOLD = 240;
const page = usePage();
const isVisible = ref(false);
const activeScrollTarget = ref<ScrollTarget | null>(null);

const testPageComponents = new Set([
    'AVEM',
    'BIT-2',
    'BRT-A',
    'BRT-B',
    'BT',
    'Exams/ExamRoom',
    'FPI-R',
    'Konzentrationstest',
    'LMT',
    'LMT2',
    'LPS',
    'MRT-A',
    'MRT-B',
]);

const isEnabledForPage = computed(() => testPageComponents.has(String(page.component ?? '')));

let animationFrameId: number | null = null;

function getWindowScrollTop() {
    return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0;
}

function isScrollableElement(target: HTMLElement) {
    return target.scrollHeight - target.clientHeight > 1;
}

function isAttachedElement(target: ScrollTarget | null): target is HTMLElement {
    return target instanceof HTMLElement && document.contains(target);
}

function getScrollTop(target: ScrollTarget) {
    if (target === window) {
        return getWindowScrollTop();
    }

    return target.scrollTop;
}

function syncVisibility(target: ScrollTarget = window) {
    if (!isEnabledForPage.value) {
        isVisible.value = false;
        activeScrollTarget.value = null;
        return;
    }

    activeScrollTarget.value = target;
    isVisible.value = getScrollTop(target) > SCROLL_THRESHOLD;
}

function queueVisibilitySync(target: ScrollTarget = window) {
    if (animationFrameId !== null) {
        window.cancelAnimationFrame(animationFrameId);
    }

    animationFrameId = window.requestAnimationFrame(() => {
        animationFrameId = null;
        syncVisibility(target);
    });
}

function handleWindowScroll() {
    queueVisibilitySync(window);
}

function handleDocumentScroll(event: Event) {
    const target = event.target;

    if (target instanceof HTMLElement && isScrollableElement(target)) {
        queueVisibilitySync(target);
    }
}

function handleResize() {
    const target = isAttachedElement(activeScrollTarget.value) ? activeScrollTarget.value : window;
    queueVisibilitySync(target);
}

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function scrollTargetToTop(target: ScrollTarget) {
    const scrollBehavior: ScrollBehavior = prefersReducedMotion() ? 'auto' : 'smooth';

    try {
        target.scrollTo({ top: 0, left: 0, behavior: scrollBehavior });
    } catch {
        if (target === window) {
            window.scrollTo(0, 0);
            return;
        }

        target.scrollTop = 0;
        target.scrollLeft = 0;
    }
}

function scrollToTop() {
    const target = isAttachedElement(activeScrollTarget.value) ? activeScrollTarget.value : window;
    scrollTargetToTop(target);
    queueVisibilitySync(target);
}

watch(
    () => page.component,
    () => queueVisibilitySync(window),
);

onMounted(() => {
    window.addEventListener('scroll', handleWindowScroll, { passive: true });
    document.addEventListener('scroll', handleDocumentScroll, true);
    window.addEventListener('resize', handleResize, { passive: true });
    queueVisibilitySync(window);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleWindowScroll);
    document.removeEventListener('scroll', handleDocumentScroll, true);
    window.removeEventListener('resize', handleResize);

    if (animationFrameId !== null) {
        window.cancelAnimationFrame(animationFrameId);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div v-if="isVisible" class="fixed right-5 bottom-5 z-[70] sm:right-6 sm:bottom-6 print:hidden">
                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button
                                type="button"
                                size="icon"
                                class="size-11 rounded-full shadow-lg"
                                aria-label="Nach oben scrollen"
                                data-testid="scroll-to-top-button"
                                @click="scrollToTop"
                            >
                                <ArrowUp class="size-5" aria-hidden="true" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="left">Nach oben</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </Transition>
    </Teleport>
</template>

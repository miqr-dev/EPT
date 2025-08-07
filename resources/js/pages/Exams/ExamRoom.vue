<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogTrigger,
} from '@/components/ui/dialog'
//import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'

// Import test components
import BRTA from '@/pages/BRT-A.vue'
import BRTB from '@/pages/BRT-B.vue'
import FPI from '@/pages/FPI-R.vue'
import LMT from '@/pages/LMT.vue'
import MRTA from '@/pages/MRT-A.vue'
import LMT2 from '@/pages/LMT2.vue'

type StepStatus = 'not_started' | 'in_progress' | 'completed'
type ExamStatus = 'not_started' | 'in_progress' | 'paused' | 'completed'

const props = defineProps<{
    exam: {
        id: number
        name: string
        status: ExamStatus
        steps: Array<{
            id: number
            name: string
            test: { id: number; name: string }
        }>
        currentStep?: {
            id: number
            test: { id: number; name: string }
        }
    }
    stepStatuses: Record<number, {
        id: number
        status: StepStatus
    }>
}>()

const activeTestComponent = ref(null)
const isTestDialogOpen = ref(false)
const activeStepId = ref<number|null>(null)

const testComponents = {
    'BRT-A': BRTA,
    'BRT-B': BRTB,
    'FPI-R': FPI,
    'LMT': LMT,
    'MRT-A': MRTA,
    'LMT2': LMT2,
}

function getStatusText(status: StepStatus) {
    const map = {
        not_started: 'Nicht gestartet',
        in_progress: 'In Bearbeitung',
        completed: 'Abgeschlossen',
    }
    return map[status]
}

function startTest(step: any) {
    activeStepId.value = step.id;
    activeTestComponent.value = testComponents[step.test.name]

    router.post('/my-exam/start-step', { exam_step_id: step.id }, {
        onSuccess: () => {
            isTestDialogOpen.value = true
            requestFullscreen()
            window.addEventListener('beforeunload', handleBeforeUnload)
            document.addEventListener('fullscreenchange', handleFullscreenChange)
        }
    })
}

function completeTest() {
    if (!activeStepId.value) return;
    router.post('/my-exam/complete-step', { exam_step_id: activeStepId.value }, {
        onSuccess: () => {
            isTestDialogOpen.value = false
            window.removeEventListener('beforeunload', handleBeforeUnload)
            document.removeEventListener('fullscreenchange', handleFullscreenChange)
            if (document.fullscreenElement) document.exitFullscreen()
            activeStepId.value = null;
            activeTestComponent.value = null;
        }
    })
}

// --- Fullscreen and Anti-Cheating ---
function requestFullscreen() {
    const elem = document.documentElement
    if (elem.requestFullscreen) elem.requestFullscreen()
}

function handleFullscreenChange() {
    if (!document.fullscreenElement) {
        alert('You have exited full-screen mode. The test will now end.')
        completeTest()
    }
}

function handleBeforeUnload(event: BeforeUnloadEvent) {
    event.preventDefault()
    event.returnValue = ''
}

// --- Polling ---
let polling: NodeJS.Timeout | null = null
onMounted(() => {
    polling = setInterval(() => {
        router.reload({ only: ['exam', 'stepStatuses'] })
    }, 5000)
})

onUnmounted(() => {
    if (polling) clearInterval(polling)
})
</script>

<template>
    <Head title="My Exam" />
    <AppLayout>
        <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-2xl p-8 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-100">{{ exam.name }}</h1>

                <!-- General Status Messages -->
                <div v-if="exam.status === 'not_started'" class="text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung hat noch nicht begonnen. Bitte warten Sie...</p>
                </div>
                <div v-else-if="exam.status === 'paused'" class="text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist pausiert. Bitte warten Sie...</p>
                </div>
                 <div v-else-if="exam.status === 'completed'" class="text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist abgeschlossen. Vielen Dank für Ihre Teilnahme.</p>
                </div>

                <!-- Exam Steps List -->
                <div v-else class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Testübersicht</h2>
                    <ul class="space-y-3">
                        <li v-for="step in exam.steps" :key="step.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-4">
                                <span class="text-lg font-medium text-gray-800 dark:text-gray-100">{{ step.test.name }}</span>
                                <Badge :class="cn(
                                    stepStatuses[step.id]?.status === 'completed' && 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    stepStatuses[step.id]?.status === 'in_progress' && 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    stepStatuses[step.id]?.status === 'not_started' && 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'
                                )">
                                    {{ getStatusText(stepStatuses[step.id]?.status) }}
                                </Badge>
                            </div>
                            <Dialog v-model:open="isTestDialogOpen">
                                <DialogTrigger as-child>
                                    <Button size="sm"
                                        :disabled="exam.currentStep?.id !== step.id || stepStatuses[step.id]?.status !== 'not_started' || exam.status !== 'in_progress'"
                                        @click="startTest(step)">
                                        Test starten
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="max-w-none w-full h-full bg-white dark:bg-gray-900 text-black dark:text-white">
                                    <component :is="activeTestComponent" @complete="completeTest" />
                                </DialogContent>
                            </Dialog>
                        </li>
                    </ul>
                    <p v-if="!exam.steps.length" class="text-center text-gray-500 dark:text-gray-400">Für diese Prüfung sind keine Tests definiert.</p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

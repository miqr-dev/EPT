<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogTrigger,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'

// Import test components
import BRTA from '@/pages/BRT-A.vue'
import BRTB from '@/pages/BRT-B.vue'
import FPI from '@/pages/FPI-R.vue'
import LMT from '@/pages/LMT.vue'
import MRTA from '@/pages/MRT-A.vue'
import LMT2 from '@/pages/LMT2.vue'

type StepStatus = 'not_started' | 'in_progress' | 'completed' | 'broken'
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
    current_step?: {
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
const activeStepId = ref<number | null>(null)
const activeTestName = ref<string | null>(null);
const page = usePage()
const userName = computed(() => page.props.auth?.user?.name)

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
    broken: 'Abgebrochen',
  } as const
  return map[status]
}

function startTest(step: any) {
  activeStepId.value = step.id;
  activeTestComponent.value = testComponents[step.test.name];
  activeTestName.value = step.test.name;

  router.post('/my-exam/start-step', { exam_step_id: step.id }, {
    onSuccess: () => {
      isTestDialogOpen.value = true
      requestFullscreen()
      window.addEventListener('beforeunload', handleBeforeUnload)
      document.addEventListener('fullscreenchange', handleFullscreenChange)
      window.addEventListener('start-finish', beginFinish as EventListener)
      window.addEventListener('cancel-finish', cancelFinish as EventListener)
    }
  })
}

function completeTest(results: any) {
  if (!activeStepId.value) return;

  const cleanup = () => {
    isTestDialogOpen.value = false
    window.removeEventListener('beforeunload', handleBeforeUnload)
    document.removeEventListener('fullscreenchange', handleFullscreenChange)
    window.removeEventListener('start-finish', beginFinish as EventListener)
    window.removeEventListener('cancel-finish', cancelFinish as EventListener)
    finishing.value = false
    if (document.fullscreenElement) document.exitFullscreen()
    activeStepId.value = null;
    activeTestComponent.value = null;
    activeTestName.value = null;
  };

  if (activeTestName.value === 'BRT-A') {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch('/tests/brt-a/score', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
      },
      body: JSON.stringify({
        answers: results.answers,
        exam_step_id: activeStepId.value
      })
    }).then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }).then(() => {
      router.post('/my-exam/complete-step', {
        exam_step_id: activeStepId.value,
      }, {
        onSuccess: cleanup,
        preserveState: true,
        preserveScroll: true,
      });
    }).catch(error => {
      console.error("Error scoring test:", error);
      // For now, we'll still try to complete the step
      router.post('/my-exam/complete-step', {
        exam_step_id: activeStepId.value,
      }, { onSuccess: cleanup });
    });
  } else {
    router.post('/my-exam/complete-step', {
      exam_step_id: activeStepId.value,
      results: results
    }, {
      onSuccess: cleanup
    })
  }
}

function breakTest() {
  if (!activeStepId.value) return;
  router.post('/my-exam/break-step', { exam_step_id: activeStepId.value }, {
    onSuccess: () => {
      isTestDialogOpen.value = false;
      window.removeEventListener('beforeunload', handleBeforeUnload);
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
      window.removeEventListener('start-finish', beginFinish as EventListener);
      window.removeEventListener('cancel-finish', cancelFinish as EventListener);
      finishing.value = false;
      if (document.fullscreenElement) document.exitFullscreen();
      activeStepId.value = null;
      activeTestComponent.value = null;
      activeTestName.value = null;
    }
  })
}

// --- Fullscreen and Anti-Cheating ---
const finishing = ref(false)
const fullscreenWarningOpen = ref(false)

function requestFullscreen() {
  const elem = document.documentElement
  if (elem.requestFullscreen) elem.requestFullscreen()
}

function handleFullscreenChange() {
  if (!document.fullscreenElement) {
    if (finishing.value) return
    fullscreenWarningOpen.value = true
  }
}

function confirmFullscreenExit() {
  fullscreenWarningOpen.value = false
  breakTest()
}

function cancelFullscreenExit() {
  fullscreenWarningOpen.value = false
  requestFullscreen()
}

function beginFinish() {
  finishing.value = true
}

function cancelFinish() {
  finishing.value = false
  requestFullscreen()
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
  <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-2xl p-8 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <h1 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-100">{{ exam.name }}</h1>

      <!-- General Status Messages -->
      <div v-if="exam.status === 'not_started'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung hat noch nicht begonnen. Bitte warten Sie...
        </p>
      </div>
      <div v-else-if="exam.status === 'paused'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">
          Die Prüfung ist pausiert. Bitte warten Sie, bis der Prüfer sie fortsetzt.
        </p>
      </div>
      <div v-else-if="exam.status === 'completed'" class="text-center">
        <p class="text-lg text-gray-600 dark:text-gray-300">Die Prüfung ist abgeschlossen. Vielen Dank für Ihre
          Teilnahme.</p>
      </div>

      <!-- Exam Steps Table -->
      <div v-else class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Testübersicht</h2>
        <div class="mt-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Test</th>
                <th scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Status</th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Aktion</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="step in exam.steps" :key="step.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{
                  step.test.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <Badge :class="cn(
                    stepStatuses[step.id]?.status === 'completed' && 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    stepStatuses[step.id]?.status === 'in_progress' && 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    stepStatuses[step.id]?.status === 'not_started' && 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'
                  )">
                    {{ getStatusText(stepStatuses[step.id]?.status) }}
                  </Badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Dialog v-model:open="isTestDialogOpen">
                    <DialogTrigger as-child>
                      <Button size="sm"
                        :disabled="exam.current_step?.id !== step.id || stepStatuses[step.id]?.status !== 'not_started' || exam.status !== 'in_progress'"
                        @click="startTest(step)">
                        Test starten
                      </Button>
                    </DialogTrigger>
                    <DialogContent
                      class="inset-0 top-0 left-0 w-screen h-screen max-w-none sm:max-w-none translate-x-0 translate-y-0 rounded-none border-none p-0 bg-white dark:bg-gray-900 text-black dark:text-white overflow-auto">
                      <template #top-right>
                        <div class="absolute top-4 right-4 font-semibold">{{ userName }}</div>
                      </template>
                      <component :is="activeTestComponent" class="w-full h-full" @complete="completeTest" />
                    </DialogContent>
                  </Dialog>
                </td>
              </tr>
              <tr v-if="!exam.steps.length">
                <td colspan="3"
                  class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                  Für diese Prüfung sind keine Tests definiert.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <Dialog :open="fullscreenWarningOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Vollbildmodus verlassen</DialogTitle>
          <DialogDescription>
            Sie haben den Vollbildmodus verlassen. Der Test wird jetzt beendet. Klicken Sie auf „Ja“, um den Test zu beenden, oder auf „Abbrechen“, um fortzufahren.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2">
          <Button variant="secondary" @click="cancelFullscreenExit">Abbrechen</Button>
          <Button variant="destructive" @click="confirmFullscreenExit">Ja</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

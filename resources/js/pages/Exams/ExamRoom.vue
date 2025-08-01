<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'

// Import test components
import BRT from '@/pages/BRT.vue'
import FPI from '@/pages/FPI.vue'
import LMT from '@/pages/LMT.vue'
import MRT from '@/pages/MRT.vue'
import LMT2 from '@/pages/LMT2.vue'


const props = defineProps<{
  exam: {
    id: number
    name: string
    status: 'not_started' | 'in_progress' | 'paused' | 'completed'
    currentStep?: {
      id: number
      test: {
        id: number
        name: string
        component_name: string
      }
    }
  }
  myStepStatus?: {
    id: number
    status: 'not_started' | 'in_progress' | 'completed'
  }
}>()

const isTestActive = computed(() => {
  return props.exam.status === 'in_progress' && props.myStepStatus?.status === 'not_started'
})

const isTestCompleted = computed(() => {
  return props.myStepStatus?.status === 'completed'
})

const isExamPaused = computed(() => {
  return props.exam.status === 'paused'
})

const isExamCompleted = computed(() => {
    return props.exam.status === 'completed';
})

const testComponent = computed(() => {
    if (!props.exam.currentStep) return null;
    const components = { BRT, FPI, LMT, MRT, LMT2 };
    return components[props.exam.currentStep.test.component_name] || null;
});

const isTestDialogOpen = ref(false)

function startTest() {
    router.post('/my-exam/start-step', { exam_step_id: props.exam.currentStep.id }, {
        onSuccess: () => {
            isTestDialogOpen.value = true;
        }
    });
}

function completeTest() {
    router.post('/my-exam/complete-step', { exam_step_id: props.exam.currentStep.id }, {
        onSuccess: () => {
            isTestDialogOpen.value = false;
        }
    });
}


let polling = null
onMounted(() => {
  polling = setInterval(() => {
    router.reload({ only: ['exam', 'myStepStatus'] })
  }, 5000)
})

onUnmounted(() => {
  if (polling) {
    clearInterval(polling)
  }
})
</script>

<template>
  <Head title="My Exam" />
  <AppLayout>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
      <div class="w-full max-w-2xl p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-center text-gray-800">{{ exam.name }}</h1>

        <!-- Waiting for exam to start -->
        <div v-if="exam.status === 'not_started'" class="text-center">
          <p class="text-lg text-gray-600">Die Prüfung hat noch nicht begonnen. Bitte warten Sie...</p>
        </div>

        <!-- Exam is paused -->
        <div v-if="isExamPaused" class="text-center">
          <p class="text-lg text-gray-600">Die Prüfung ist pausiert. Bitte warten Sie...</p>
        </div>

        <!-- Test is active, show start button -->
        <div v-if="isTestActive" class="text-center">
            <Dialog v-model:open="isTestDialogOpen">
                <DialogTrigger as-child>
                    <Button size="lg" @click="startTest">Test starten: {{ exam.currentStep.test.name }}</Button>
                </DialogTrigger>
                <DialogContent class="max-w-none w-full h-full text-white bg-gray-900">
                    <component :is="testComponent" @complete="completeTest" />
                </DialogContent>
            </Dialog>
        </div>

        <!-- Test is completed, waiting for next -->
        <div v-if="isTestCompleted" class="text-center">
          <p class="text-lg text-gray-600">Sie haben den aktuellen Test abgeschlossen. Bitte warten Sie, bis der nächste Test freigeschaltet wird.</p>
        </div>

        <!-- Exam is completed -->
        <div v-if="isExamCompleted" class="text-center">
          <p class="text-lg text-gray-600">Die Prüfung ist abgeschlossen. Vielen Dank für Ihre Teilnahme.</p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

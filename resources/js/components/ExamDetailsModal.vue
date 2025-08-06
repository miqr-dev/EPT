<script setup lang="ts">
import { ref, watch } from 'vue'
import draggable from 'vuedraggable'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  exam: any
  show: boolean
}>()

const emit = defineEmits(['close', 'save'])

const localSteps = ref([])

watch(() => props.exam, (newExam) => {
  if (newExam && newExam.steps) {
    localSteps.value = [...newExam.steps].sort((a, b) => a.step_order - b.step_order)
  } else {
    localSteps.value = []
  }
})

function closeModal() {
  emit('close')
}
function removeStep(stepId: number) {
  localSteps.value = localSteps.value.filter((step) => step.id !== stepId)
}

function saveChanges() {
  emit('save', localSteps.value)
}

function startExam() {
  if (!props.exam) return
  router.post(`/exams/${props.exam.id}/start`, {}, {
    onSuccess: () => {
      closeModal()
    }
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-2xl border border-gray-200 dark:border-gray-700">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ exam.name }}</h2>
        <button @click="closeModal" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&times;</button>
      </div>
      <div>
        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Participants</h3>
        <ul class="text-gray-700 dark:text-gray-300">
          <li v-for="p in exam.participants" :key="p.id">{{ p.user.name }}, {{ p.user.firstname }}</li>
        </ul>
      </div>
      <div class="mt-4">
        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">Steps (Drag to re-order)</h3>
        <draggable v-model="localSteps" item-key="id" class="space-y-2">
          <template #item="{ element }">
            <div class="p-2 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700 cursor-move text-ray-800 dark:text-gray-200">
              {{ element.test.name }}
            </div>
          </template>
        </draggable>
      </div>
      <div class="mt-6 flex justify-end gap-2">
        <button @click="closeModal" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-ray-300 dark:hover:bg-gray-500">Close</button>
        <button @click="saveChanges" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
        <button v-if="exam && exam.status === 'not_started'" @click="startExam" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Start Exam
        </button>
      </div>
    </div>
  </div>
</template>

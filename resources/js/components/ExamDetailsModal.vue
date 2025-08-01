<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps<{
  exam: any
  show: boolean
}>()

const emit = defineEmits(['close', 'save'])

const localSteps = ref([])

watch(() => props.exam, (newExam) => {
  if (newExam) {
    localSteps.value = [...newExam.steps].sort((a, b) => a.step_order - b.step_order)
  }
})

function closeModal() {
  emit('close')
}

function saveChanges() {
  emit('save', localSteps.value)
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">{{ exam.name }}</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-800">&times;</button>
      </div>
      <div>
        <h3 class="font-semibold mb-2">Participants</h3>
        <ul>
          <li v-for="p in exam.participants" :key="p.id">{{ p.user.name }}, {{ p.user.firstname }}</li>
        </ul>
      </div>
      <div class="mt-4">
        <h3 class="font-semibold mb-2">Steps (Drag to re-order)</h3>
        <draggable v-model="localSteps" item-key="id" class="space-y-2">
          <template #item="{ element }">
            <div class="p-2 border rounded-md bg-gray-50 cursor-move">
              {{ element.test.name }}
            </div>
          </template>
        </draggable>
      </div>
      <div class="mt-6 flex justify-end gap-2">
        <button @click="closeModal" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Close</button>
        <button @click="saveChanges" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </div>
  </div>
</template>

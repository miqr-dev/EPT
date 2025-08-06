<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  cities: Array<any>
  teachers: Array<any>
  tests: Array<any>
}>()

const emit = defineEmits(['cancel'])

const form = useForm({
  name: '',
  city_id: null,
  teacher_id: null,
  steps: [],
})

function addStep() {
  form.steps.push({ test_id: null, duration: 60 })
}

function removeStep(index) {
  form.steps.splice(index, 1)
}

function saveExam() {
  form.post('/exams', {
    onSuccess: () => {
      emit('cancel')
    }
  })
}
</script>

<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Create Exam</h2>
    <form @submit.prevent="saveExam" class="space-y-6">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Exam Name</label>
        <input type="text" v-model="form.name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
      </div>
      <div>
        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
        <select v-model="form.city_id" id="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
          <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
        </select>
      </div>
      <div>
        <label for="teacher" class="block text-sm font-medium text-gray-700">Teacher</label>
        <select v-model="form.teacher_id" id="teacher" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
          <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option>
        </select>
      </div>
      <div>
        <h3 class="text-lg font-medium text-gray-700">Exam Steps</h3>
        <div v-for="(step, index) in form.steps" :key="index" class="flex items-center space-x-4 mt-2">
          <select v-model="step.test_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            <option v-for="test in tests" :key="test.id" :value="test.id">{{ test.name }}</option>
          </select>
          <input type="number" v-model="step.duration" class="block w-1/4 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
          <button type="button" @click="removeStep(index)" class="text-red-600 hover:underline">Remove</button>
        </div>
        <button type="button" @click="addStep" class="mt-2 text-blue-600 hover:underline">Add Step</button>
      </div>
      <div class="flex justify-end space-x-4">
        <button type="button" @click="emit('cancel')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md font-semibold hover:bg-gray-300 transition">
          Cancel
        </button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition" :disabled="form.processing">
          Save Exam
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
  cities: Array<{ id: number; name: string }>
  teachers: Array<{ id: number; name: string; firstname: string }>
  tests: Array<{ id: number; name: string }>
}>()

const form = ref({
  name: '',
  city_id: '',
  teacher_id: '',
  steps: [] as Array<{ test_id: string; duration: number }>,
})

const errors = ref<{ [key: string]: any }>({})

function addStep() {
  form.value.steps.push({ test_id: '', duration: 45 })
}

function removeStep(index: number) {
  form.value.steps.splice(index, 1)
}

function submit() {
  errors.value = {}
  router.post('/exams', form.value, {
    onError: err => errors.value = err,
  })
}
</script>

<template>
  <Head title="Neue Prüfung anlegen" />
  <AppLayout>
    <div class="max-w-xl mx-auto mt-10 px-4">
      <div class="bg-white shadow rounded-xl border border-gray-100 p-8">
        <h1 class="text-2xl font-bold mb-7 text-gray-800">Neue Prüfung anlegen</h1>
        <form @submit.prevent="submit" class="space-y-6">
          <!-- General Info -->
          <div>
            <label class="block mb-1 font-medium text-gray-700">Titel</label>
            <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" required />
            <div v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block mb-1 font-medium text-gray-700">Stadt</label>
              <select v-model="form.city_id" class="w-full border rounded px-3 py-2" required>
                <option disabled value="">Bitte wählen</option>
                <option v-for="city in props.cities" :key="city.id" :value="city.id">{{ city.name }}</option>
              </select>
              <div v-if="errors.city_id" class="text-sm text-red-500 mt-1">{{ errors.city_id }}</div>
            </div>
            <div>
              <label class="block mb-1 font-medium text-gray-700">Prüfer:in</label>
              <select v-model="form.teacher_id" class="w-full border rounded px-3 py-2" required>
                <option disabled value="">Bitte wählen</option>
                <option v-for="teacher in props.teachers" :key="teacher.id" :value="teacher.id">
                  {{ teacher.firstname }} {{ teacher.name }}
                </option>
              </select>
              <div v-if="errors.teacher_id" class="text-sm text-red-500 mt-1">{{ errors.teacher_id }}</div>
            </div>
          </div>

          <!-- Steps -->
          <div class="border-t pt-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="font-semibold text-gray-700">Prüfungsschritte</h2>
              <button type="button" @click="addStep" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm font-semibold hover:bg-blue-200">
                Schritt hinzufügen
              </button>
            </div>
            <div v-if="errors.steps" class="text-sm text-red-500 my-2">{{ errors.steps }}</div>
            <div class="space-y-3">
              <div v-for="(step, index) in form.steps" :key="index" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span class="font-bold text-gray-500">{{ index + 1 }}.</span>
                <div class="flex-1">
                  <label class="text-xs text-gray-600">Test</label>
                  <select v-model="step.test_id" class="w-full border-gray-300 rounded text-sm py-1" required>
                    <option disabled value="">Test auswählen</option>
                    <option v-for="test in props.tests" :key="test.id" :value="test.id">{{ test.name }}</option>
                  </select>
                   <div v-if="errors[`steps.${index}.test_id`]" class="text-sm text-red-500 mt-1">{{ errors[`steps.${index}.test_id`] }}</div>
                </div>
                <div class="w-24">
                  <label class="text-xs text-gray-600">Dauer (Min)</label>
                  <input v-model.number="step.duration" type="number" class="w-full border-gray-300 rounded text-sm py-1" required min="1" />
                  <div v-if="errors[`steps.${index}.duration`]" class="text-sm text-red-500 mt-1">{{ errors[`steps.${index}.duration`] }}</div>
                </div>
                <button type="button" @click="removeStep(index)" class="text-red-500 hover:text-red-700 mt-4">&#10005;</button>
              </div>
              <div v-if="!form.steps.length" class="text-center py-4 text-gray-400">
                Noch keine Schritte hinzugefügt.
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 border-t pt-6 mt-8">
            <Link href="/exams" class="bg-gray-100 text-gray-600 px-4 py-2 rounded hover:bg-gray-200 transition">Abbrechen</Link>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold transition">
              Prüfung anlegen
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

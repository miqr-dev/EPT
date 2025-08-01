<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps<{
  cities: Array<{ id: number; name: string }>
}>()

const form = ref({
  name: '',
  city_id: '',
})

const errors = ref<{ [key: string]: string[] }>({})

function submit() {
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
          <div>
            <label class="block mb-1 font-medium text-gray-700">Titel</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full border rounded px-3 py-2"
              placeholder="z.B. Abiturprüfung 2025"
              required
            />
            <div v-if="errors.value.name" class="text-sm text-red-500 mt-1">
              {{ errors.value.name[0] }}
            </div>
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700">Stadt</label>
            <select v-model="form.city_id" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>Bitte wählen</option>
              <option v-for="city in props.cities" :key="city.id" :value="city.id">
                {{ city.name }}
              </option>
            </select>
            <div v-if="errors.value.city_id" class="text-sm text-red-500 mt-1">
              {{ errors.value.city_id[0] }}
            </div>
          </div>
          <div class="flex justify-end gap-3">
            <Link href="/exams" class="bg-gray-100 text-gray-600 px-4 py-2 rounded hover:bg-gray-200 transition">Abbrechen</Link>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold transition">
              Anlegen
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

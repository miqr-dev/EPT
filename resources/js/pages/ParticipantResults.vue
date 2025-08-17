<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps<{
  results: Array<{ id: number; test_name: string; result_json: any }>
}>()

const editing = ref<{ id: number; content: string } | null>(null)

function startEdit(res: { id: number; result_json: any }) {
  editing.value = { id: res.id, content: JSON.stringify(res.result_json, null, 2) }
}

function cancelEdit() {
  editing.value = null
}

function saveEdit() {
  if (!editing.value) return
  try {
    const parsed = JSON.parse(editing.value.content)
    router.put(`/participant/results/${editing.value.id}`, { result_json: parsed }, {
      onSuccess: () => {
        editing.value = null
      }
    })
  } catch (e) {
    alert('Invalid JSON')
  }
}
</script>

<template>
  <Head title="Ergebnisse" />
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
    <div class="max-w-4xl mx-auto p-4">
      <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">Meine Testergebnisse</h1>
      <div v-if="!props.results.length" class="text-gray-600 dark:text-gray-400">Keine Ergebnisse vorhanden.</div>
      <div v-else class="space-y-4">
        <div v-for="res in props.results" :key="res.id" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow flex justify-between items-start">
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ res.test_name }}</h2>
            <pre class="mt-1 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ JSON.stringify(res.result_json, null, 2) }}</pre>
          </div>
          <button @click="startEdit(res)" class="ml-4 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Bearbeiten</button>
        </div>
      </div>
    </div>

    <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-2xl">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Ergebnis bearbeiten</h2>
        <textarea v-model="editing.content" rows="15" class="w-full p-2 border rounded bg-gray-50 dark:bg-gray-900 dark:text-gray-100"></textarea>
        <div class="mt-4 flex justify-end space-x-2">
          <button @click="cancelEdit" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">Abbrechen</button>
          <button @click="saveEdit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Speichern</button>
        </div>
      </div>
    </div>
  </div>
</template>

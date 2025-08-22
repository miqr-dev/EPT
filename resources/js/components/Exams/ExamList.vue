<script setup lang="ts">
const props = defineProps<{
  exams: Array<any>
}>()

const emit = defineEmits(['create-exam', 'view-exam'])
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">Prüfungen</h2>
      <button @click="emit('create-exam')"
        class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition">
        Prüfung erstellen
      </button>
    </div>
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Stadt</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prüfer:in</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aktionen</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="exam in exams" :key="exam.id">
            <td class="whitespace-nowrap px-6 py-4">{{ exam.name }}</td>
            <td class="whitespace-nowrap px-6 py-4">{{ exam.city.name }}</td>
            <td class="whitespace-nowrap px-6 py-4">{{ exam.teacher.name }}</td>
            <td class="whitespace-nowrap px-6 py-4">{{ exam.status }}</td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              <button @click="emit('view-exam', exam)" class="text-blue-600 hover:underline">
                Anzeigen
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

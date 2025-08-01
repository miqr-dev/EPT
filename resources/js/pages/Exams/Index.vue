<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  exams: Array<{
    id: number
    name: string
    city?: { name: string }
    status?: string
    participants_count?: number
    created_at?: string
  }>
}>()
</script>

<template>
  <Head title="Alle Prüfungen" />
  <AppLayout>
    <div class="max-w-5xl mx-auto mt-8 px-4">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Prüfungen</h1>
        <Link href="/exams/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold shadow transition">
          Neue Prüfung erstellen
        </Link>
      </div>
      <div class="bg-white shadow rounded-xl border border-gray-100 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Titel</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Stadt</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700">Teilnehmer</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="exam in props.exams" :key="exam.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">{{ exam.name }}</td>
              <td class="px-4 py-3">{{ exam.city?.name ?? '-' }}</td>
              <td class="px-4 py-3 capitalize">{{ exam.status ?? 'Neu' }}</td>
              <td class="px-4 py-3">{{ exam.participants_count ?? 0 }}</td>
              <td class="px-4 py-3 text-right">
                <Link :href="`/exams/${exam.id}`" class="text-blue-600 font-semibold hover:underline">Verwalten</Link>
              </td>
            </tr>
            <tr v-if="!props.exams.length">
              <td colspan="5" class="text-center py-6 text-gray-400">Keine Prüfungen gefunden.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

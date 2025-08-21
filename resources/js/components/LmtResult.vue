<script setup lang="ts">
const { results } = defineProps<{ results: any }>()

function formatTime(sec: number | null | undefined): string {
  if (sec == null || isNaN(sec)) return '–'
  const s = Number(sec)
  if (s < 60) return `${s} s`
  const m = Math.floor(s / 60)
  const rest = s % 60
  return rest ? `${m}m ${rest}s` : `${m}m`
}

const scales = [
  { key: 'L1', label: 'L1' },
  { key: 'L2', label: 'L2' },
  { key: 'F-', label: 'F−' },
  { key: 'F+', label: 'F⁺' },
]
</script>

<template>
  <div class="p-6 bg-background border rounded-lg">
    <h2 class="text-xl font-semibold mb-4">Test abgeschlossen!</h2>
    <div class="mb-6 w-full max-w-md">
      <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
        <tbody>
          <tr class="bg-muted/40">
            <td class="font-semibold px-3 py-2 w-1/2">Gesamtdauer</td>
            <td class="px-3 py-2">{{ formatTime(results.total_time_seconds) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="overflow-x-auto mb-6">
      <table class="min-w-full text-sm border rounded shadow">
        <thead class="bg-muted/40">
          <tr>
            <th class="p-2 text-left">Skala</th>
            <th class="p-2 text-left">Rohwert</th>
            <th class="p-2 text-left">T-Wert</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="scale in scales" :key="scale.key" class="border-t">
            <td class="p-2 font-semibold">{{ scale.label }}</td>
            <td class="p-2">{{ results.group_scores?.[scale.key] ?? '–' }}</td>
            <td class="p-2">{{ results.group_t_values?.[scale.key] ?? '–' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="results.answers" class="overflow-x-auto">
      <table class="min-w-full text-sm border rounded shadow">
        <thead class="bg-muted/40">
          <tr>
            <th class="p-2 text-left">#</th>
            <th class="p-2 text-left">Antwort</th>
            <th class="p-2 text-left">Gruppe</th>
            <th class="p-2 text-left">Punkte</th>
            <th class="p-2 text-left">Zeit</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(ans, idx) in results.answers" :key="idx" class="border-t">
            <td class="p-2">{{ ans.number }}</td>
            <td class="p-2">{{ ans.selected_category ?? '–' }}</td>
            <td class="p-2">{{ ans.selected_group ?? '–' }}</td>
            <td class="p-2">{{ ans.points ?? '–' }}</td>
            <td class="p-2">{{ formatTime(ans.time_seconds) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


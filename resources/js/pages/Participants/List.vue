<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TestResultModal from '@/components/TestResultModal.vue';
import { Checkbox } from '@/components/ui/checkbox';

defineProps<{
  participants: {
    data: any[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);
const selectedParticipant = ref(null);
const togglingIds = ref<number[]>([]);

const dateFormatter = computed(() =>
  new Intl.DateTimeFormat('de-DE', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
);

function viewTestResult(assignment, participant) {
  selectedAssignment.value = assignment;
  selectedParticipant.value = participant;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedAssignment.value = null;
  selectedParticipant.value = null;
}

const isToggling = (id: number) => togglingIds.value.includes(id);

function toggleLoginPermission(participant: any, value: boolean | string | undefined) {
  const nextValue = Boolean(value);
  if (participant.can_login === nextValue) return;

  togglingIds.value.push(participant.id);

  router.patch(
    route('participants.login-permission', participant.id),
    { can_login: nextValue },
    {
      preserveScroll: true,
      onFinish: () => {
        togglingIds.value = togglingIds.value.filter((id) => id !== participant.id);
      },
    },
  );
}
</script>

<template>
  <AppLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Card>
        <CardHeader>
          <CardTitle>Teilnehmer</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Prüfung erstellt am</TableHead>
                <TableHead>Tests</TableHead>
                <TableHead class="text-center">Login erlaubt</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="participants.data.length === 0">
                <TableCell colspan="4" class="text-center text-gray-500 py-4">Keine Teilnehmer:innen gefunden.</TableCell>
              </TableRow>
              <TableRow v-for="participant in participants.data" :key="participant.id">
                <TableCell>{{ participant.name }}</TableCell>
                <TableCell>
                  <span v-if="participant.latest_exam_created_at">
                    {{ dateFormatter.format(new Date(participant.latest_exam_created_at)) }}
                  </span>
                  <span v-else>–</span>
                </TableCell>
                <TableCell>
                  <div v-if="participant.test_assignments.length > 0">
                    <div class="flex flex-wrap gap-3">
                      <button v-for="assignment in participant.test_assignments" :key="assignment.id"
                        class="rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-gray-100 disabled:text-gray-400"
                        :disabled="assignment.results.length === 0"
                        @click="assignment.results.length > 0 && viewTestResult(assignment, participant)">
                        {{ assignment.test.name }}
                      </button>
                    </div>
                  </div>
                  <div v-else>
                    Keine Tests zugewiesen.
                  </div>
                </TableCell>
                <TableCell>
                  <label class="flex items-center justify-center gap-3">
                    <Checkbox :checked="participant.can_login" :disabled="isToggling(participant.id)"
                      @update:checked="(value) => toggleLoginPermission(participant, value)" />
                    <span class="text-sm text-gray-700">{{ participant.can_login ? 'Aktiviert' : 'Gesperrt' }}</span>
                  </label>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div v-if="participants.links.length > 3" class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <template v-for="(link, key) in participants.links" :key="key">
                <component
                  :is="link.url ? Link : 'span'"
                  v-bind="link.url ? { href: link.url } : {}"
                  class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                  :class="{
                    'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                    'rounded-l-md': key === 0,
                    'rounded-r-md': key === participants.links.length - 1,
                    'hidden md:inline-flex': !(link.active || key === 0 || key === participants.links.length - 1 || (link.label.includes('Previous') || link.label.includes('Next'))),
                    'cursor-default text-gray-400': !link.url,
                  }"
                  :aria-disabled="!link.url"
                >
                  <span v-html="link.label" />
                </component>
              </template>
            </nav>
          </div>
        </CardContent>
      </Card>
    </div>
    <TestResultModal :isOpen="isModalOpen" :assignment="selectedAssignment" :participant="selectedParticipant"
      @close="closeModal" />
  </AppLayout>
</template>

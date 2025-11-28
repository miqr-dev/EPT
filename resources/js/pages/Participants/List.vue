<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import TestResultModal from '@/components/TestResultModal.vue';

defineProps<{
  participants: any[];
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);
const selectedParticipant = ref(null);

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
                <TableHead>Tests</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="participant in participants" :key="participant.id">
                <TableCell>{{ participant.name }}</TableCell>
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
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
    <TestResultModal :isOpen="isModalOpen" :assignment="selectedAssignment" :participant="selectedParticipant"
      @close="closeModal" />
  </AppLayout>
</template>

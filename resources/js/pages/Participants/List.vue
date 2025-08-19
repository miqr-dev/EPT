<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import TestResultModal from '@/components/TestResultModal.vue';

const props = defineProps<{
    participants: any[];
}>();

const isModalOpen = ref(false);
const selectedAssignment = ref(null);

function viewTestResult(assignment) {
    selectedAssignment.value = assignment;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedAssignment.value = null;
}
</script>

<template>
    <AppLayout>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Card>
                <CardHeader>
                    <CardTitle>Participants</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Tests</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="participant in participants" :key="participant.id">
                                <TableCell>{{ participant.name }}</TableCell>
                                <TableCell>{{ participant.email }}</TableCell>
                                <TableCell>
                                    <div v-if="participant.test_assignments.length > 0">
                                        <div v-for="assignment in participant.test_assignments" :key="assignment.id" class="mb-2">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-semibold">{{ assignment.test.name }}</p>
                                                    <Badge :variant="assignment.status === 'completed' ? 'default' : 'secondary'">
                                                        {{ assignment.status }}
                                                    </Badge>
                                                </div>
                                                <div v-if="assignment.results.length > 0">
                                                    <Button @click="viewTestResult(assignment)" size="sm">
                                                        View Results
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        No tests assigned.
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
        <TestResultModal :isOpen="isModalOpen" :assignment="selectedAssignment" @close="closeModal" />
    </AppLayout>
</template>

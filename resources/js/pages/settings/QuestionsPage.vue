<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'; // Assuming a main layout
import Heading from '@/components/Heading.vue'; // Assuming a Heading component
import { Button } from '@/components/ui/button'; // Assuming a Button component
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'; // Assuming Table components
import { PlusCircle, Edit, Trash2 } from 'lucide-vue-next'; // Icons

// Define props passed from the controller (e.g., questions, filters)
const props = defineProps<{
  questions: {
    data: Array<{ id: number; text: string; type: string; test: { name: string } | null }>, // Adjust based on your Question model and relationships
    links: Array<{ url: string | null; label: string; active: boolean }>, // For pagination
  };
  filters: {
    search: string | null;
  };
  // tests: Array<{ id: number; name: string }>; // If you pass tests for a dropdown
}>();

const searchQuery = ref(props.filters.search || '');

// Function to search/filter questions
const searchQuestions = () => {
  router.get(route('questions.index'), { search: searchQuery.value }, {
    preserveState: true,
    replace: true,
  });
};

// Form for creating/editing a question - this might be moved to a modal/dialog later
const form = useForm({
  id: null as number | null,
  text: '',
  type: 'multiple_choice', // Default type
  options: [] as string[],
  answer: '',
  test_id: null as number | null,
});

// Function to open a modal/form for creating a new question (placeholder)
const openCreateModal = () => {
  form.reset();
  // Logic to show a modal, for now, we can navigate to a create page if one exists
  // For simplicity, let's assume a create route `questions.create` exists
  // router.visit(route('questions.create'));
  console.log('Open create modal/form');
};

// Function to open a modal/form for editing a question (placeholder)
const openEditModal = (question: any) => {
  form.id = question.id;
  form.text = question.text;
  form.type = question.type;
  form.options = question.options || [];
  form.answer = question.answer;
  form.test_id = question.test_id;
  // Logic to show a modal
  console.log('Open edit modal/form for question:', question.id);
};

// Function to delete a question
const deleteQuestion = (id: number) => {
  if (confirm('Are you sure you want to delete this question?')) {
    router.delete(route('questions.destroy', id), {
      preserveScroll: true,
      onSuccess: () => {
        // Add any success message or UI update here
      },
    });
  }
};

</script>

<template>
  <Head title="Manage Questions" />
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <Heading level="1">Manage Questions</Heading>
        <Button @click="openCreateModal">
          <PlusCircle class="h-5 w-5 mr-2" />
          Add Question
        </Button>
      </div>

      <!-- Search Bar -->
      <div class="mb-4">
        <form @submit.prevent="searchQuestions">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search questions..."
            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </form>
      </div>

      <!-- Questions Table -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Text</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Assigned Test</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="questions.data.length === 0">
              <TableCell colspan="4" class="text-center text-gray-500 py-4">No questions found.</TableCell>
            </TableRow>
            <TableRow v-for="question in questions.data" :key="question.id">
              <TableCell>{{ question.text }}</TableCell>
              <TableCell>{{ question.type }}</TableCell>
              <TableCell>{{ question.test?.name || 'N/A' }}</TableCell>
              <TableCell class="text-right">
                <Button variant="ghost" size="icon" @click="openEditModal(question)" class="mr-2">
                  <Edit class="h-4 w-4" />
                </Button>
                <Button variant="ghost" size="icon" @click="deleteQuestion(question.id)" class="text-red-600 hover:text-red-700">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="questions.links.length > 3" class="mt-6 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <template v-for="(link, key) in questions.links" :key="key">
            <Link
              :href="link.url!"
              v-html="link.label"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              :class="{
                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                'rounded-l-md': key === 0,
                'rounded-r-md': key === questions.links.length - 1,
                'hidden md:inline-flex': !(link.active || key === 0 || key === questions.links.length - 1 || (link.label.includes('Previous') || link.label.includes('Next'))) // Basic responsive pagination
              }"
              :disabled="!link.url"
            />
          </template>
        </nav>
      </div>

      <!-- Modal/Form for Create/Edit would go here or be a separate component -->
      <!-- For now, we've just console.logged for openCreateModal and openEditModal -->

    </div>
  </AppLayout>
</template>

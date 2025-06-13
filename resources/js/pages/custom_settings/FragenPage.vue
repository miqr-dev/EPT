<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { PlusCircle, Edit, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
  questions: {
    data: Array<{ id: number; text: string; type: string; options: string[] | null; answer: string; test_id: number | null; test: { name: string } | null }>,
    links: Array<{ url: string | null; label: string; active: boolean }>,
  };
  filters: {
    search: string | null;
  };
  tests: Array<{ id: number; name: string }>;
}>();

const searchQuery = ref(props.filters.search || '');
const searchQuestions = () => {
  router.get(route('einstellungen.fragen'), { search: searchQuery.value }, {
    preserveState: true, replace: true, preserveScroll: true,
  });
};

const showModal = ref(false);
const isEditMode = ref(false);
const optionsInput = ref(''); // For textarea binding for multiple choice options

const form = useForm({
  id: null as number | null,
  text: '',
  type: 'multiple_choice' as 'multiple_choice' | 'true_false' | 'short_answer',
  options: [] as string[],
  answer: '',
  test_id: null as number | null,
});

const stringToOptions = (optionsString: string): string[] => {
  return optionsString.split('\n').map(opt => opt.trim()).filter(opt => opt.length > 0);
};

const openCreateModal = () => {
  isEditMode.value = false;
  form.reset();
  optionsInput.value = '';
  showModal.value = true;
};

const openEditModal = (question: typeof props.questions.data[0]) => {
  isEditMode.value = true;
  form.id = question.id;
  form.text = question.text;
  form.type = question.type as 'multiple_choice' | 'true_false' | 'short_answer';
  form.answer = question.answer;
  form.test_id = question.test_id;
  if (question.type === 'multiple_choice' && question.options) {
    optionsInput.value = question.options.join('\n');
    form.options = question.options;
  } else {
    optionsInput.value = '';
    form.options = [];
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
  form.clearErrors();
  optionsInput.value = '';
};

const submitForm = () => {
  if (form.type === 'multiple_choice') {
    form.options = stringToOptions(optionsInput.value);
  } else {
    form.options = [];
  }

  const routeName = isEditMode.value ? 'einstellungen.questions.update' : 'einstellungen.questions.store';
  const params = isEditMode.value && form.id ? [form.id] : [];

  form.submit(isEditMode.value ? 'put' : 'post', route(routeName, ...params), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  });
};

const deleteQuestion = (id: number) => {
  if (confirm('Sind Sie sicher, dass Sie diese Frage löschen möchten?')) {
    router.delete(route('einstellungen.questions.destroy', id), {
      preserveScroll: true,
    });
  }
};

</script>

<template>
  <Head title="Fragen Management" />
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <Heading level="1">Fragen Management</Heading>
        <Button @click="openCreateModal">
          <PlusCircle class="h-5 w-5 mr-2" />
          Frage hinzufügen
        </Button>
      </div>

      <div class="mb-4">
        <Input
            type="text"
            v-model="searchQuery"
            placeholder="Fragen suchen..."
            @input="searchQuestions"
            class="block w-full"
        />
      </div>

      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Text</TableHead>
              <TableHead>Typ</TableHead>
              <TableHead>Test</TableHead>
              <TableHead class="text-right">Aktionen</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="!questions.data || questions.data.length === 0">
              <TableCell colspan="4" class="text-center text-gray-500 py-4">Keine Fragen gefunden.</TableCell>
            </TableRow>
            <TableRow v-for="question in questions.data" :key="question.id">
              <TableCell class="max-w-xs truncate" :title="question.text">{{ question.text }}</TableCell>
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

      <div v-if="questions.links.length > 3" class="mt-6 flex justify-center">
        <template v-for="(link, key) in questions.links" :key="key">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
            :class="{
              'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
              'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
            }"
          />
          <span v-else v-html="link.label" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium text-gray-400"/>
        </template>
      </div>

      <Dialog :open="showModal" @update:open="showModal = $event">
        <DialogContent class="sm:max-w-[600px]" @escape-key-down="closeModal" @interact-outside="closeModal">
          <DialogHeader>
            <DialogTitle>{{ isEditMode ? 'Frage bearbeiten' : 'Neue Frage hinzufügen' }}</DialogTitle>
          </DialogHeader>
          <form @submit.prevent="submitForm">
            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="text" class="text-right">Text</Label>
                <Input id="text" v-model="form.text" class="col-span-3" />
                <InputError :message="form.errors.text" class="col-span-3 col-start-2" />
              </div>
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="type" class="text-right">Typ</Label>
                <Select v-model="form.type">
                  <SelectTrigger class="col-span-3"><SelectValue placeholder="Fragetyp auswählen" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="multiple_choice">Multiple Choice</SelectItem>
                    <SelectItem value="true_false">Richtig/Falsch</SelectItem>
                    <SelectItem value="short_answer">Kurzantwort</SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.type" class="col-span-3 col-start-2" />
              </div>
              <div v-if="form.type === 'multiple_choice'" class="grid grid-cols-4 items-center gap-4">
                <Label for="options" class="text-right">Optionen</Label>
                <textarea id="options" v-model="optionsInput" class="col-span-3 border rounded-md p-2 h-24" placeholder="Jede Option in einer neuen Zeile"></textarea>
                <InputError :message="form.errors.options" class="col-span-3 col-start-2" />
              </div>
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="answer" class="text-right">Antwort</Label>
                <Input id="answer" v-model="form.answer" class="col-span-3" />
                <InputError :message="form.errors.answer" class="col-span-3 col-start-2" />
              </div>
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="test_id" class="text-right">Test zuordnen</Label>
                <Select v-model="form.test_id">
                  <SelectTrigger class="col-span-3"><SelectValue placeholder="Test auswählen (optional)" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="null">Keiner</SelectItem>
                    <SelectItem v-for="testItem in tests" :key="testItem.id" :value="testItem.id">{{ testItem.name }}</SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.test_id" class="col-span-3 col-start-2" />
              </div>
            </div>
            <DialogFooter>
              <Button type="button" variant="outline" @click="closeModal">Abbrechen</Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? (isEditMode ? 'Speichern...' : 'Erstellen...') : (isEditMode ? 'Änderungen speichern' : 'Frage erstellen') }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

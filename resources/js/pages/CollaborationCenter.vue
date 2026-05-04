<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Check, Plus, ThumbsDown, ThumbsUp, Trash2 } from 'lucide-vue-next';

defineProps<{ newsItems: any[]; todos: any[]; suggestions: any[] }>();
const role = computed(() => (usePage().props.auth as any).user.role);

const newsForm = useForm({ title: '', content: '' });
const todoForm = useForm({ task: '' });
const suggestionForm = useForm({ title: '', content: '' });
const voteForm = useForm({ vote: '', comment: '' });
const dislikeCommentBySuggestion = ref<Record<number, string>>({});

const canManageNews = computed(() => role.value === 'admin');
const canManageTodos = computed(() => role.value === 'admin');
const canPromote = computed(() => role.value === 'admin');

const postNews = () => newsForm.post(route('collaboration.news.store'), { onSuccess: () => newsForm.reset() });
const postTodo = () => todoForm.post(route('collaboration.todos.store'), { onSuccess: () => todoForm.reset() });
const postSuggestion = () => suggestionForm.post(route('collaboration.suggestions.store'), { onSuccess: () => suggestionForm.reset() });

const submitVote = (suggestionId: number, vote: 'like' | 'dislike' | null) => {
  const comment = vote === 'dislike' ? (dislikeCommentBySuggestion.value[suggestionId] ?? '') : '';
  voteForm.transform(() => ({ vote, comment })).post(route('collaboration.suggestions.vote', suggestionId));
};
</script>

<template>
  <div class="space-y-4 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Kollaboration</h1>
        <p class="text-sm text-muted-foreground">News, Aufgaben und Vorschläge für Lehrkräfte und Admins.</p>
      </div>
    </div>

    <div class="grid h-[calc(100vh-11rem)] grid-cols-1 gap-4 overflow-hidden xl:grid-cols-3">
      <Card class="flex min-h-0 flex-col">
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle>News & Updates</CardTitle>
          <Dialog v-if="canManageNews">
            <DialogTrigger as-child>
              <Button size="icon" variant="outline"><Plus class="h-4 w-4" /></Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader><DialogTitle>Neue News</DialogTitle><DialogDescription>Nur für Admins sichtbar.</DialogDescription></DialogHeader>
              <div class="space-y-3">
                <Input v-model="newsForm.title" placeholder="Titel" />
                <Textarea v-model="newsForm.content" placeholder="Inhalt" />
              </div>
              <DialogFooter><Button @click="postNews">Speichern</Button></DialogFooter>
            </DialogContent>
          </Dialog>
        </CardHeader>
        <CardContent class="min-h-0 space-y-3 overflow-y-auto">
          <div v-for="item in newsItems" :key="item.id" class="rounded-lg border p-3">
            <div class="mb-2 flex items-start justify-between gap-2">
              <h3 class="font-semibold">{{ item.title }}</h3>
              <Button v-if="canManageNews" size="icon" variant="ghost" @click="useForm({}).delete(route('collaboration.news.delete', item.id))"><Trash2 class="h-4 w-4 text-red-600" /></Button>
            </div>
            <p class="text-sm text-muted-foreground">{{ item.content }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="flex min-h-0 flex-col">
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle>Todos</CardTitle>
          <Dialog v-if="canManageTodos">
            <DialogTrigger as-child>
              <Button size="icon" variant="outline"><Plus class="h-4 w-4" /></Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader><DialogTitle>Neue Aufgabe</DialogTitle></DialogHeader>
              <Textarea v-model="todoForm.task" placeholder="Aufgabe beschreiben" />
              <DialogFooter><Button @click="postTodo">Speichern</Button></DialogFooter>
            </DialogContent>
          </Dialog>
        </CardHeader>
        <CardContent class="min-h-0 space-y-3 overflow-y-auto">
          <div v-for="todo in todos" :key="todo.id" class="flex items-start gap-3 rounded-lg border p-3">
            <Button v-if="canManageTodos" size="icon" variant="outline" @click="useForm({ is_completed: !todo.is_completed }).patch(route('collaboration.todos.update', todo.id))"><Check class="h-4 w-4" /></Button>
            <div class="flex-1">
              <p :class="{ 'line-through text-muted-foreground': todo.is_completed }">{{ todo.task }}</p>
              <Badge :variant="todo.is_completed ? 'secondary' : 'default'">{{ todo.is_completed ? 'Erledigt' : 'Offen' }}</Badge>
            </div>
            <Button v-if="canManageTodos" size="icon" variant="ghost" @click="useForm({}).delete(route('collaboration.todos.delete', todo.id))"><Trash2 class="h-4 w-4 text-red-600" /></Button>
          </div>
        </CardContent>
      </Card>

      <Card class="flex min-h-0 flex-col">
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle>Vorschläge</CardTitle>
          <Dialog>
            <DialogTrigger as-child>
              <Button size="icon" variant="outline"><Plus class="h-4 w-4" /></Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader><DialogTitle>Vorschlag erstellen</DialogTitle></DialogHeader>
              <div class="space-y-3">
                <Input v-model="suggestionForm.title" placeholder="Titel" />
                <Textarea v-model="suggestionForm.content" placeholder="Beschreibung" />
              </div>
              <DialogFooter><Button @click="postSuggestion">Senden</Button></DialogFooter>
            </DialogContent>
          </Dialog>
        </CardHeader>
        <CardContent class="min-h-0 space-y-3 overflow-y-auto">
          <div v-for="s in suggestions" :key="s.id" class="space-y-2 rounded-lg border p-3" :class="s.status === 'promoted' ? 'border-green-500 bg-green-50/50' : ''">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold">{{ s.title }}</h3>
              <Badge :variant="s.status === 'promoted' ? 'secondary' : 'outline'">{{ s.status === 'promoted' ? 'In Aufgaben' : 'Offen' }}</Badge>
            </div>
            <p class="text-sm text-muted-foreground">{{ s.content }}</p>
            <div class="flex flex-wrap gap-2">
              <Button size="sm" variant="outline" @click="submitVote(s.id, 'like')"><ThumbsUp class="mr-1 h-4 w-4" />Like</Button>
              <Button size="sm" variant="outline" @click="submitVote(s.id, 'dislike')"><ThumbsDown class="mr-1 h-4 w-4" />Dislike</Button>
              <Button size="sm" variant="ghost" @click="submitVote(s.id, null)">Stimme entfernen</Button>
              <Button v-if="canPromote" size="sm" @click="useForm({}).post(route('collaboration.suggestions.promote', s.id))">In Aufgaben übernehmen</Button>
              <Button v-if="canPromote" size="sm" variant="secondary" @click="useForm({}).post(route('collaboration.suggestions.hide', s.id))">Verbergen</Button>
            </div>
            <Textarea v-model="dislikeCommentBySuggestion[s.id]" placeholder="Kommentar bei Dislike" class="text-sm" />
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

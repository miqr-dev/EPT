<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { Check, Pencil, Plus, ThumbsDown, ThumbsUp, Trash2 } from 'lucide-vue-next';

defineProps<{ newsItems: any[]; todos: any[]; suggestions: any[] }>();
const role = computed(() => (usePage().props.auth as any).user.role);

const canManageNews = computed(() => role.value === 'admin');
const canManageTodos = computed(() => role.value === 'admin');

const newsForm = useForm({ title: '', content: '' });
const editNewsForm = useForm({ title: '', content: '' });
const todoForm = useForm({ task: '' });
const editTodoForm = useForm({ task: '' });
const suggestionForm = useForm({ content: '' });
const voteForm = useForm({ vote: '', comment: '' });
const dislikeCommentBySuggestion = ref<Record<number, string>>({});

const editingNewsId = ref<number | null>(null);
const editingTodoId = ref<number | null>(null);
const formatter = new Intl.DateTimeFormat('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

const postNews = () => newsForm.post(route('collaboration.news.store'), { onSuccess: () => newsForm.reset() });
const updateNews = () => editingNewsId.value && editNewsForm.patch(route('collaboration.news.update', editingNewsId.value), { onSuccess: () => (editingNewsId.value = null) });
const postTodo = () => todoForm.post(route('collaboration.todos.store'), { onSuccess: () => todoForm.reset() });
const updateTodoText = () => editingTodoId.value && editTodoForm.patch(route('collaboration.todos.update', editingTodoId.value), { onSuccess: () => (editingTodoId.value = null) });
const postSuggestion = () => suggestionForm.post(route('collaboration.suggestions.store'), { onSuccess: () => suggestionForm.reset() });
const submitVote = (id: number, vote: 'like' | 'dislike' | null) => voteForm.transform(() => ({ vote, comment: vote === 'dislike' ? (dislikeCommentBySuggestion.value[id] ?? '') : '' })).post(route('collaboration.suggestions.vote', id));

const openNewsEdit = (item: any) => { editingNewsId.value = item.id; editNewsForm.title = item.title; editNewsForm.content = item.content; };
const openTodoEdit = (item: any) => { editingTodoId.value = item.id; editTodoForm.task = item.task; };
</script>

<template>
  <Head title="Kollaboration" />
  <AppLayout>
    <div class="space-y-5 p-6">
      <h1 class="text-2xl font-bold">Kollaboration</h1>

      <Card class="border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50 to-rose-50">
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>News & Updates</CardTitle>
          <Dialog v-if="canManageNews"><DialogTrigger as-child><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger><DialogContent><DialogHeader><DialogTitle>Update veröffentlichen</DialogTitle></DialogHeader><Input v-model="newsForm.title" placeholder="Titel" /><Textarea v-model="newsForm.content" placeholder="Information" /><DialogFooter><Button @click="postNews">Speichern</Button></DialogFooter></DialogContent></Dialog>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
            <div v-for="item in newsItems" :key="item.id" class="rounded-xl border border-amber-200 bg-white/90 p-3 shadow-sm">
              <div class="mb-2 flex items-start justify-between gap-2"><h3 class="font-semibold text-amber-900">{{ item.title }}</h3><div class="flex gap-1" v-if="canManageNews"><Button size="icon" variant="ghost" @click="openNewsEdit(item)"><Pencil class="h-4 w-4" /></Button><Button size="icon" variant="ghost" @click="useForm({}).delete(route('collaboration.news.delete', item.id))"><Trash2 class="h-4 w-4 text-red-600" /></Button></div></div>
              <p class="text-sm text-slate-700">{{ item.content }}</p>
              <p class="mt-3 text-xs text-slate-500">{{ formatter.format(new Date(item.created_at)) }} · von {{ item.author?.name }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <Card class="border-blue-300 bg-blue-50/60">
          <CardHeader class="flex flex-row items-center justify-between"><CardTitle class="text-blue-900">Todos</CardTitle><Dialog v-if="canManageTodos"><DialogTrigger as-child><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger><DialogContent><DialogHeader><DialogTitle>Todo anlegen</DialogTitle><DialogDescription>Für alle sichtbar.</DialogDescription></DialogHeader><Textarea v-model="todoForm.task" placeholder="Aufgabe" /><DialogFooter><Button @click="postTodo">Speichern</Button></DialogFooter></DialogContent></Dialog></CardHeader>
          <CardContent class="space-y-3">
            <div v-for="todo in todos" :key="todo.id" class="rounded-lg border border-blue-200 bg-white p-3">
              <div class="flex items-start gap-2"><Button v-if="canManageTodos" size="icon" variant="outline" @click="useForm({ is_completed: !todo.is_completed }).patch(route('collaboration.todos.update', todo.id))"><Check class="h-4 w-4" /></Button><p class="flex-1" :class="{ 'line-through text-slate-500': todo.is_completed }">{{ todo.task }}</p><Badge :variant="todo.is_completed ? 'secondary' : 'outline'">{{ todo.is_completed ? 'Erledigt' : 'Aktiv' }}</Badge><Button v-if="canManageTodos" size="icon" variant="ghost" @click="openTodoEdit(todo)"><Pencil class="h-4 w-4" /></Button><Button v-if="canManageTodos" size="icon" variant="ghost" @click="useForm({}).delete(route('collaboration.todos.delete', todo.id))"><Trash2 class="h-4 w-4 text-red-600" /></Button></div>
              <p class="mt-2 text-xs text-slate-500">{{ formatter.format(new Date(todo.created_at)) }} · von {{ todo.author?.name }}</p>
            </div>
          </CardContent>
        </Card>

        <Card class="border-violet-300 bg-violet-50/50">
          <CardHeader class="flex flex-row items-center justify-between"><CardTitle class="text-violet-900">Vorschläge</CardTitle><Dialog><DialogTrigger as-child><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger><DialogContent><DialogHeader><DialogTitle>Vorschlag erstellen</DialogTitle><DialogDescription>Ohne Titel, kurz und konkret.</DialogDescription></DialogHeader><Textarea v-model="suggestionForm.content" placeholder="Dein Vorschlag" /><DialogFooter><Button @click="postSuggestion">Senden</Button></DialogFooter></DialogContent></Dialog></CardHeader>
          <CardContent class="space-y-3">
            <div v-for="s in suggestions" :key="s.id" class="rounded-lg border border-violet-200 bg-white p-3">
              <p class="text-sm text-slate-700">{{ s.content }}</p>
              <p class="mt-2 text-xs text-slate-500">{{ formatter.format(new Date(s.created_at)) }} · von {{ s.author?.name }}</p>
              <div class="mt-2 flex flex-wrap gap-2"><Button size="sm" variant="outline" @click="submitVote(s.id, 'like')"><ThumbsUp class="mr-1 h-4 w-4" />Like</Button><Button size="sm" variant="outline" @click="submitVote(s.id, 'dislike')"><ThumbsDown class="mr-1 h-4 w-4" />Dislike</Button><Button size="sm" variant="ghost" @click="submitVote(s.id, null)">Stimme entfernen</Button><Button v-if="canManageTodos" size="sm" @click="useForm({}).post(route('collaboration.suggestions.promote', s.id))">In Aufgaben übernehmen</Button><Button v-if="canManageTodos" size="sm" variant="secondary" @click="useForm({}).post(route('collaboration.suggestions.hide', s.id))">Verbergen</Button></div>
              <Textarea v-model="dislikeCommentBySuggestion[s.id]" placeholder="Kommentar bei Dislike" class="mt-2 text-sm" />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <Dialog :open="editingNewsId !== null" @update:open="(o) => { if (!o) editingNewsId = null }">
      <DialogContent><DialogHeader><DialogTitle>News bearbeiten</DialogTitle></DialogHeader><Input v-model="editNewsForm.title" /><Textarea v-model="editNewsForm.content" /><DialogFooter><Button @click="updateNews">Änderungen speichern</Button></DialogFooter></DialogContent>
    </Dialog>

    <Dialog :open="editingTodoId !== null" @update:open="(o) => { if (!o) editingTodoId = null }">
      <DialogContent><DialogHeader><DialogTitle>Todo bearbeiten</DialogTitle></DialogHeader><Textarea v-model="editTodoForm.task" /><DialogFooter><Button @click="updateTodoText">Änderungen speichern</Button></DialogFooter></DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ newsItems: any[]; todos: any[]; suggestions: any[] }>();
const role = (usePage().props.auth as any).user.role;

const newsForm = useForm({ title: '', content: '' });
const todoForm = useForm({ task: '' });
const suggestionForm = useForm({ title: '', content: '' });
const voteForm = useForm({ vote: '', comment: '' });
</script>

<template>
  <div class="p-6 space-y-8">
    <h1 class="text-2xl font-bold">Kollaboration</h1>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold">News & Updates</h2>
      <form v-if="role === 'admin'" @submit.prevent="newsForm.post(route('collaboration.news.store'))" class="space-y-2 rounded border p-3">
        <input v-model="newsForm.title" class="w-full rounded border p-2" placeholder="Titel" />
        <textarea v-model="newsForm.content" class="w-full rounded border p-2" placeholder="Inhalt" />
        <button class="rounded bg-black px-3 py-2 text-white">Veröffentlichen</button>
      </form>
      <div v-for="item in newsItems" :key="item.id" class="rounded border p-3">
        <div class="flex items-center justify-between"><h3 class="font-semibold">{{ item.title }}</h3><button v-if="role === 'admin'" @click="useForm({}).delete(route('collaboration.news.delete', item.id))" class="text-red-600">Löschen</button></div>
        <p>{{ item.content }}</p>
      </div>
    </section>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold">Todos</h2>
      <form v-if="role === 'admin'" @submit.prevent="todoForm.post(route('collaboration.todos.store'))" class="space-y-2 rounded border p-3">
        <textarea v-model="todoForm.task" class="w-full rounded border p-2" placeholder="Aufgabe" />
        <button class="rounded bg-black px-3 py-2 text-white">Anlegen</button>
      </form>
      <div v-for="todo in todos" :key="todo.id" class="rounded border p-3 flex items-center gap-3">
        <input type="checkbox" :checked="todo.is_completed" :disabled="role !== 'admin'" @change="useForm({is_completed: !todo.is_completed}).patch(route('collaboration.todos.update', todo.id))" />
        <p :class="{ 'line-through text-gray-500': todo.is_completed }">{{ todo.task }}</p>
        <button v-if="role === 'admin'" @click="useForm({}).delete(route('collaboration.todos.delete', todo.id))" class="ml-auto text-red-600">Löschen</button>
      </div>
    </section>

    <section class="space-y-4">
      <h2 class="text-xl font-semibold">Vorschläge</h2>
      <form @submit.prevent="suggestionForm.post(route('collaboration.suggestions.store'))" class="space-y-2 rounded border p-3">
        <input v-model="suggestionForm.title" class="w-full rounded border p-2" placeholder="Titel" />
        <textarea v-model="suggestionForm.content" class="w-full rounded border p-2" placeholder="Beschreibung" />
        <button class="rounded bg-black px-3 py-2 text-white">Vorschlag senden</button>
      </form>
      <div v-for="s in suggestions" :key="s.id" class="rounded border p-3 space-y-2" :class="s.status === 'promoted' ? 'border-green-500 bg-green-50' : ''">
        <h3 class="font-semibold">{{ s.title }}</h3>
        <p>{{ s.content }}</p>
        <p class="text-sm text-gray-600">Status: {{ s.status === 'promoted' ? 'In Aufgaben übernommen' : 'Offen' }}</p>
        <div class="flex gap-2">
          <button @click="voteForm.transform(() => ({ vote: 'like', comment: '' })).post(route('collaboration.suggestions.vote', s.id))" class="rounded border px-2 py-1">👍</button>
          <button @click="voteForm.transform(() => ({ vote: 'dislike', comment: voteForm.comment })).post(route('collaboration.suggestions.vote', s.id))" class="rounded border px-2 py-1">👎</button>
          <button @click="voteForm.transform(() => ({ vote: null, comment: '' })).post(route('collaboration.suggestions.vote', s.id))" class="rounded border px-2 py-1">Vote entfernen</button>
          <button v-if="role==='admin'" @click="useForm({}).post(route('collaboration.suggestions.promote', s.id))" class="rounded bg-blue-600 px-2 py-1 text-white">In Aufgaben übernehmen</button>
          <button v-if="role==='admin'" @click="useForm({}).post(route('collaboration.suggestions.hide', s.id))" class="rounded bg-gray-700 px-2 py-1 text-white">Verbergen</button>
        </div>
        <textarea v-model="voteForm.comment" class="w-full rounded border p-2" placeholder="Kommentar (bei Dislike)" />
      </div>
    </section>
  </div>
</template>

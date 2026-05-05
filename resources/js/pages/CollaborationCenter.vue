<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Check, Pencil, Plus, ThumbsDown, ThumbsUp, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{ newsItems: any[]; todos: any[]; suggestions: any[] }>();
const pageUser = computed(() => (usePage().props.auth as any).user);
const role = computed(() => pageUser.value.role);

const canManageNews = computed(() => role.value === 'admin');
const canManageTodos = computed(() => role.value === 'admin');

const newsForm = useForm({ title: '', content: '' });
const editNewsForm = useForm({ title: '', content: '' });
const todoForm = useForm({ task: '' });
const editTodoForm = useForm({ task: '' });
const suggestionForm = useForm({ content: '' });
const dislikeCommentBySuggestion = ref<Record<number, string>>({});
const showDislikeCommentInput = ref<Record<number, boolean>>({});

const editingNewsId = ref<number | null>(null);
const editingTodoId = ref<number | null>(null);
const formatter = new Intl.DateTimeFormat('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

const postNews = () => newsForm.post(route('collaboration.news.store'), { onSuccess: () => newsForm.reset() });
const updateNews = () =>
    editingNewsId.value &&
    editNewsForm.patch(route('collaboration.news.update', editingNewsId.value), { onSuccess: () => (editingNewsId.value = null) });
const postTodo = () => todoForm.post(route('collaboration.todos.store'), { onSuccess: () => todoForm.reset() });
const updateTodoText = () =>
    editingTodoId.value &&
    editTodoForm.patch(route('collaboration.todos.update', editingTodoId.value), { onSuccess: () => (editingTodoId.value = null) });
const postSuggestion = () => suggestionForm.post(route('collaboration.suggestions.store'), { onSuccess: () => suggestionForm.reset() });
const submitVote = (id: number, vote: 'like' | 'dislike' | null) => {
    if (vote === 'dislike') {
        showDislikeCommentInput.value[id] = true;
    }
    if (vote === 'like' || vote === null) {
        showDislikeCommentInput.value[id] = false;
    }
    router.post(
        route('collaboration.suggestions.vote', id),
        {
            vote,
            comment: vote === 'dislike' ? (dislikeCommentBySuggestion.value[id] ?? '') : '',
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                dislikeCommentBySuggestion.value[id] = '';
                showDislikeCommentInput.value[id] = false;
            },
        },
    );
};

const openNewsEdit = (item: any) => {
    editingNewsId.value = item.id;
    editNewsForm.title = item.title;
    editNewsForm.content = item.content;
};
const openTodoEdit = (item: any) => {
    editingTodoId.value = item.id;
    editTodoForm.task = item.task;
};
const votesFor = (suggestion: any, type: 'like' | 'dislike') => (suggestion.votes || []).filter((v: any) => v.vote === type);
const voteCount = (suggestion: any, type: 'like' | 'dislike') => votesFor(suggestion, type).length;
const voteNames = (suggestion: any, type: 'like' | 'dislike') =>
    votesFor(suggestion, type)
        .map((v: any) => v.user?.name)
        .filter(Boolean)
        .join(', ');
const myVote = (suggestion: any) => (suggestion.votes || []).find((v: any) => v.user_id === pageUser.value.id)?.vote ?? null;
const myDislikeComment = (suggestion: any) =>
    (suggestion.votes || []).find((v: any) => v.user_id === pageUser.value.id && v.vote === 'dislike')?.comment ?? '';
const canVoteOn = (suggestion: any) => suggestion.created_by !== pageUser.value.id;
const submitDislikeComment = (id: number) => {
    router.post(
        route('collaboration.suggestions.vote', id),
        {
            vote: 'dislike',
            comment: dislikeCommentBySuggestion.value[id] ?? '',
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                dislikeCommentBySuggestion.value[id] = '';
                showDislikeCommentInput.value[id] = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Kollaboration" />
    <AppLayout>
        <div class="space-y-6 p-6">
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5 shadow-sm">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Kollaboration</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-600">
                    Professionelle Teamfläche für Informationsaustausch, gemeinsame Vorschläge und transparente Aufgabenplanung.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">
                <section class="2xl:col-span-7">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Info</h2>
                            <p class="text-sm text-slate-500">Neuigkeiten und Updates für Lehrkräfte.</p>
                        </div>
                        <Dialog v-if="canManageNews"
                            ><DialogTrigger as-child
                                ><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger
                            ><DialogContent
                                ><DialogHeader><DialogTitle>Update veröffentlichen</DialogTitle></DialogHeader
                                ><Input v-model="newsForm.title" placeholder="Titel" /><Textarea
                                    v-model="newsForm.content"
                                    placeholder="Information"
                                /><DialogFooter><Button @click="postNews">Speichern</Button></DialogFooter></DialogContent
                            ></Dialog
                        >
                    </div>
                    <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <article v-for="item in newsItems" :key="item.id" class="pb-4">
                            <div class="mb-1 flex items-start justify-between gap-3">
                                <h3 class="text-lg font-semibold text-slate-900">{{ item.title }}</h3>
                                <div class="flex gap-1" v-if="canManageNews">
                                    <Button size="icon" variant="ghost" @click="openNewsEdit(item)"><Pencil class="h-4 w-4" /></Button
                                    ><Button
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            () => {
                                                if (window.confirm('News wirklich löschen?'))
                                                    useForm({}).delete(route('collaboration.news.delete', item.id));
                                            }
                                        "
                                        ><Trash2 class="h-4 w-4 text-red-600"
                                    /></Button>
                                </div>
                            </div>
                            <p class="text-base leading-7 text-slate-800">{{ item.content }}</p>
                            <p class="mt-2 text-right text-sm text-slate-500">
                                {{ formatter.format(new Date(item.created_at)) }} · von {{ item.author?.name }}
                            </p>
                            <hr class="mt-4 border-slate-200" />
                        </article>
                    </div>
                </section>

                <section class="2xl:col-span-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Vorschläge</h2>
                            <p class="text-sm text-slate-500">Ideen einreichen und direkt im Team bewerten.</p>
                        </div>
                        <Dialog
                            ><DialogTrigger as-child
                                ><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger
                            ><DialogContent
                                ><DialogHeader
                                    ><DialogTitle>Vorschlag erstellen</DialogTitle
                                    ><DialogDescription>Ohne Titel, kurz und konkret.</DialogDescription></DialogHeader
                                ><Textarea v-model="suggestionForm.content" placeholder="Dein Vorschlag" /><DialogFooter
                                    ><Button @click="postSuggestion">Senden</Button></DialogFooter
                                ></DialogContent
                            ></Dialog
                        >
                    </div>
                    <Card class="h-full rounded-2xl border-slate-200 bg-white shadow-sm">
                        <CardContent class="space-y-3 p-5">
                            <div v-for="s in suggestions" :key="s.id" class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p class="text-base text-slate-800">{{ s.content }}</p>
                                <p class="mt-2 text-right text-xs text-slate-500">
                                    {{ formatter.format(new Date(s.created_at)) }} · von {{ s.author?.name }}
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <Button
                                        size="sm"
                                        :variant="myVote(s) === 'like' ? 'default' : 'outline'"
                                        :class="myVote(s) === 'like' ? 'bg-blue-600 text-white hover:bg-blue-700' : ''"
                                        :title="
                                            canVoteOn(s)
                                                ? voteNames(s, 'like') || 'Noch keine Likes'
                                                : 'Eigene Vorschläge können nicht bewertet werden'
                                        "
                                        :disabled="!canVoteOn(s)"
                                        @click="submitVote(s.id, 'like', s)"
                                        ><ThumbsUp class="mr-1 h-4 w-4" />Like{{ voteCount(s, 'like') ? ` ${voteCount(s, 'like')}` : '' }}</Button
                                    >
                                    <Button
                                        size="sm"
                                        :variant="myVote(s) === 'dislike' ? 'secondary' : 'outline'"
                                        :title="
                                            canVoteOn(s)
                                                ? voteNames(s, 'dislike') || 'Noch keine Dislikes'
                                                : 'Eigene Vorschläge können nicht bewertet werden'
                                        "
                                        :disabled="!canVoteOn(s)"
                                        @click="submitVote(s.id, 'dislike', s)"
                                        ><ThumbsDown class="mr-1 h-4 w-4" />Dislike{{
                                            voteCount(s, 'dislike') ? ` ${voteCount(s, 'dislike')}` : ''
                                        }}</Button
                                    >
                                    <Button
                                        v-if="s.created_by === pageUser.id"
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            () => {
                                                if (window.confirm('Vorschlag wirklich löschen?'))
                                                    useForm({}).delete(route('collaboration.suggestions.delete', s.id));
                                            }
                                        "
                                        ><Trash2 class="h-4 w-4 text-red-600"
                                    /></Button>
                                    <Button
                                        v-if="canManageTodos"
                                        size="sm"
                                        @click="useForm({}).post(route('collaboration.suggestions.promote', s.id))"
                                        >In Aufgaben übernehmen</Button
                                    >
                                    <Button
                                        v-if="canManageTodos"
                                        size="sm"
                                        variant="secondary"
                                        @click="useForm({}).post(route('collaboration.suggestions.hide', s.id))"
                                        >Verbergen</Button
                                    >
                                </div>

                                <div v-if="showDislikeCommentInput[s.id] && !myDislikeComment(s)" class="mt-2 space-y-2">
                                    <Textarea v-model="dislikeCommentBySuggestion[s.id]" placeholder="what's the problem?" class="text-sm" />
                                    <Button size="sm" variant="secondary" @click="submitDislikeComment(s.id)">Kommentar speichern</Button>
                                </div>

                                <div v-if="votesFor(s, 'dislike').length" class="mt-2 space-y-1 rounded-md border border-slate-200 bg-slate-50 p-2">
                                    <div v-for="v in votesFor(s, 'dislike').filter((x: any) => x.comment)" :key="`comment-${v.id}`" class="space-y-1">
                                        <p class="text-xs font-medium text-slate-700">{{ v.user?.name }}</p>
                                        <div class="rounded-md border border-slate-200 bg-white p-2 text-xs text-slate-700">{{ v.comment }}</div>
                                        <p class="text-right text-[11px] text-slate-500">
                                            {{ formatter.format(new Date(v.updated_at || v.created_at)) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <section class="2xl:col-span-12">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Todo-Liste</h2>
                            <p class="text-sm text-slate-500">Transparenz darüber, woran aktuell gearbeitet wird.</p>
                        </div>
                        <Dialog v-if="canManageTodos"
                            ><DialogTrigger as-child
                                ><Button size="icon"><Plus class="h-4 w-4" /></Button></DialogTrigger
                            ><DialogContent
                                ><DialogHeader
                                    ><DialogTitle>Todo anlegen</DialogTitle><DialogDescription>Für alle sichtbar.</DialogDescription></DialogHeader
                                ><Textarea v-model="todoForm.task" placeholder="Aufgabe" /><DialogFooter
                                    ><Button @click="postTodo">Speichern</Button></DialogFooter
                                ></DialogContent
                            ></Dialog
                        >
                    </div>
                    <Card class="rounded-2xl border-slate-200 bg-white shadow-sm">
                        <CardContent class="space-y-3 p-5">
                            <div v-for="todo in todos" :key="todo.id" class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-start gap-2">
                                    <Button
                                        v-if="canManageTodos"
                                        size="icon"
                                        variant="outline"
                                        @click="useForm({ is_completed: !todo.is_completed }).patch(route('collaboration.todos.update', todo.id))"
                                        ><Check class="h-4 w-4"
                                    /></Button>
                                    <p class="flex-1" :class="{ 'text-slate-500 line-through': todo.is_completed }">{{ todo.task }}</p>
                                    <Badge :variant="todo.is_completed ? 'secondary' : 'outline'">{{
                                        todo.is_completed ? 'Erledigt' : 'Aktiv'
                                    }}</Badge
                                    ><Button v-if="canManageTodos" size="icon" variant="ghost" @click="openTodoEdit(todo)"
                                        ><Pencil class="h-4 w-4" /></Button
                                    ><Button
                                        v-if="canManageTodos"
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            () => {
                                                if (window.confirm('Todo wirklich löschen?'))
                                                    useForm({}).delete(route('collaboration.todos.delete', todo.id));
                                            }
                                        "
                                        ><Trash2 class="h-4 w-4 text-red-600"
                                    /></Button>
                                </div>
                                <p class="mt-2 text-right text-xs text-slate-500">
                                    {{ formatter.format(new Date(todo.created_at)) }} · von {{ todo.author?.name }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </div>

        <Dialog
            :open="editingNewsId !== null"
            @update:open="
                (o) => {
                    if (!o) editingNewsId = null;
                }
            "
        >
            <DialogContent
                ><DialogHeader><DialogTitle>News bearbeiten</DialogTitle></DialogHeader
                ><Input v-model="editNewsForm.title" /><Textarea v-model="editNewsForm.content" /><DialogFooter
                    ><Button @click="updateNews">Änderungen speichern</Button></DialogFooter
                ></DialogContent
            >
        </Dialog>

        <Dialog
            :open="editingTodoId !== null"
            @update:open="
                (o) => {
                    if (!o) editingTodoId = null;
                }
            "
        >
            <DialogContent
                ><DialogHeader><DialogTitle>Todo bearbeiten</DialogTitle></DialogHeader
                ><Textarea v-model="editTodoForm.task" /><DialogFooter
                    ><Button @click="updateTodoText">Änderungen speichern</Button></DialogFooter
                ></DialogContent
            >
        </Dialog>
    </AppLayout>
</template>

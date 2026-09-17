<script setup lang="ts">
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Check, Circle, Pencil, Plus, ThumbsDown, ThumbsUp, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ newsItems: any[]; todos: any[]; suggestions: any[] }>();
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
const showNewsDialog = ref(false);
const showTodoDialog = ref(false);
const showSuggestionDialog = ref(false);
const activeTab = ref<'news' | 'suggestions' | 'todos'>('news');

const NEW_ITEM_WINDOW_MS = 14 * 24 * 60 * 60 * 1000;
const formatter = new Intl.DateTimeFormat('de-DE', { dateStyle: 'medium' });
const dateColumnFormatter = new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: 'short' });
const timeFormatter = new Intl.DateTimeFormat('de-DE', { hour: '2-digit', minute: '2-digit' });

const tabItems = computed(() => [
    { value: 'news' as const, label: 'News', count: props.newsItems.length },
    { value: 'suggestions' as const, label: 'Vorschläge', count: props.suggestions.length },
    { value: 'todos' as const, label: 'Todos', count: props.todos.length },
]);

const activeItemsCount = computed(() => tabItems.value.find((item) => item.value === activeTab.value)?.count ?? 0);

const isNewItem = (item: any) => {
    const createdAt = new Date(item.created_at).getTime();

    return Number.isFinite(createdAt) && Date.now() - createdAt < NEW_ITEM_WINDOW_MS;
};

const newUntilLabel = (item: any) => {
    const createdAt = new Date(item.created_at).getTime();

    if (!Number.isFinite(createdAt)) {
        return '';
    }

    return `Neu bis ${formatter.format(new Date(createdAt + NEW_ITEM_WINDOW_MS))}`;
};

const datePartsFor = (item: any) => {
    const date = new Date(item.created_at);

    if (Number.isNaN(date.getTime())) {
        return { top: '', bottom: '' };
    }

    const today = new Date();
    const isToday = date.toDateString() === today.toDateString();

    return {
        top: isToday ? 'Heute' : dateColumnFormatter.format(date).replace('.', ''),
        bottom: isToday ? timeFormatter.format(date) : String(date.getFullYear()),
    };
};

const authorWithCity = (author: any) => {
    if (!author) return '';

    const cityName = author.city?.name ?? author.city_name ?? author.city ?? '';
    const city = cityName ? ` - ${cityName}` : '';

    return `${author.name ?? ''}${city}`;
};

const postNews = () =>
    newsForm.post(route('collaboration.news.store'), {
        onSuccess: () => {
            newsForm.reset();
            showNewsDialog.value = false;
        },
    });
const updateNews = () =>
    editingNewsId.value &&
    editNewsForm.patch(route('collaboration.news.update', editingNewsId.value), {
        onSuccess: () => (editingNewsId.value = null),
    });
const postTodo = () =>
    todoForm.post(route('collaboration.todos.store'), {
        onSuccess: () => {
            todoForm.reset();
            showTodoDialog.value = false;
        },
    });
const updateTodoText = () =>
    editingTodoId.value &&
    editTodoForm.patch(route('collaboration.todos.update', editingTodoId.value), {
        onSuccess: () => (editingTodoId.value = null),
    });
const postSuggestion = () =>
    suggestionForm.post(route('collaboration.suggestions.store'), {
        onSuccess: () => {
            suggestionForm.reset();
            showSuggestionDialog.value = false;
        },
    });

const submitVote = (id: number, vote: 'like' | 'dislike' | null, suggestion: any) => {
    const currentVote = myVote(suggestion);

    if (vote === currentVote) {
        return;
    }

    if (vote === 'like') {
        const currentComment = myDislikeComment(suggestion);

        if (currentComment && !window.confirm('Wenn Sie zu "Like" wechseln, wird Ihr Kommentar gelöscht. Fortfahren?')) {
            return;
        }
    }

    if (vote === 'dislike') {
        showDislikeCommentInput.value[id] = true;
        dislikeCommentBySuggestion.value[id] = myDislikeComment(suggestion) || '';

        return;
    }

    router.post(
        route('collaboration.suggestions.vote', id),
        { vote },
        {
            preserveScroll: true,
            preserveState: false,
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
            preserveState: false,
            onSuccess: () => {
                dislikeCommentBySuggestion.value[id] = '';
                showDislikeCommentInput.value[id] = false;
            },
        },
    );
};

const deleteNews = (id: number) => {
    if (!window.confirm('News wirklich löschen?')) return;

    router.delete(route('collaboration.news.delete', id));
};

const deleteSuggestion = (id: number) => {
    if (!window.confirm('Vorschlag wirklich löschen?')) return;

    router.delete(route('collaboration.suggestions.delete', id));
};

const deleteTodo = (id: number) => {
    if (!window.confirm('Todo wirklich löschen?')) return;

    router.delete(route('collaboration.todos.delete', id));
};

const toggleTodoCompleted = (todo: any) => {
    router.patch(route('collaboration.todos.update', todo.id), {
        is_completed: !todo.is_completed,
    });
};

const promoteSuggestion = (id: number) => {
    router.post(route('collaboration.suggestions.promote', id));
};
</script>

<template>
    <Head title="Kollaboration" />
    <AppLayout>
        <main class="min-h-full bg-slate-50/70 px-6 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
            <div class="mx-auto max-w-5xl">
                <p class="text-xs text-slate-400 dark:text-slate-500">Kollaboration &gt; Ankündigungen</p>

                <div
                    class="mt-5 flex flex-col gap-4 border-b border-slate-200 pb-1 sm:flex-row sm:items-end sm:justify-between dark:border-slate-800"
                >
                    <nav class="flex gap-8" aria-label="Kollaboration">
                        <button
                            v-for="tab in tabItems"
                            :key="tab.value"
                            type="button"
                            class="border-b-2 pb-3 text-sm transition-colors"
                            :class="
                                activeTab === tab.value
                                    ? 'border-slate-950 font-semibold text-slate-950 dark:border-white dark:text-white'
                                    : 'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100'
                            "
                            @click="activeTab = tab.value"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>

                    <div class="flex items-center gap-2 pb-2">
                        <Dialog v-if="activeTab === 'news' && canManageNews" :open="showNewsDialog" @update:open="(val) => (showNewsDialog = val)">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <Plus class="h-4 w-4" />
                                    Update
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle>Update veröffentlichen</DialogTitle>
                                </DialogHeader>
                                <Input v-model="newsForm.title" placeholder="Titel" />
                                <RichTextEditor v-model="newsForm.content" placeholder="Update schreiben..." />
                                <DialogFooter>
                                    <Button @click="postNews">Speichern</Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <Dialog v-if="activeTab === 'suggestions'" :open="showSuggestionDialog" @update:open="(val) => (showSuggestionDialog = val)">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <Plus class="h-4 w-4" />
                                    Vorschlag
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Vorschlag erstellen</DialogTitle>
                                    <DialogDescription>Ohne Titel, kurz und konkret.</DialogDescription>
                                </DialogHeader>
                                <Textarea v-model="suggestionForm.content" placeholder="Dein Vorschlag" />
                                <DialogFooter>
                                    <Button @click="postSuggestion">Senden</Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <Dialog v-if="activeTab === 'todos' && canManageTodos" :open="showTodoDialog" @update:open="(val) => (showTodoDialog = val)">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <Plus class="h-4 w-4" />
                                    Todo
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Todo anlegen</DialogTitle>
                                    <DialogDescription>Für alle sichtbar.</DialogDescription>
                                </DialogHeader>
                                <Textarea v-model="todoForm.task" placeholder="Aufgabe" />
                                <DialogFooter>
                                    <Button @click="postTodo">Speichern</Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div class="mt-7">
                    <section v-if="activeTab === 'news'" aria-label="Neuigkeiten und Updates">
                        <article
                            v-for="item in newsItems"
                            :key="item.id"
                            class="group grid gap-4 border-b border-slate-200 py-5 md:grid-cols-[4.5rem_1fr_auto] dark:border-slate-800"
                        >
                            <time
                                class="text-xs leading-5"
                                :class="isNewItem(item) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-500'"
                            >
                                <span class="block">{{ datePartsFor(item).top }}</span>
                                <span class="block">{{ datePartsFor(item).bottom }}</span>
                            </time>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2
                                        class="text-[17px] leading-6 font-bold"
                                        :class="isNewItem(item) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-950 dark:text-white'"
                                    >
                                        {{ item.title }}
                                    </h2>
                                    <Badge
                                        v-if="isNewItem(item)"
                                        variant="outline"
                                        class="rounded-md border-emerald-200 bg-emerald-600 px-2 py-0 text-[11px] text-white dark:border-emerald-700 dark:bg-emerald-500 dark:text-emerald-950"
                                        :title="newUntilLabel(item)"
                                    >
                                        Neu
                                    </Badge>
                                </div>
                                <div class="announcement-copy mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400" v-html="item.content"></div>
                                <p v-if="authorWithCity(item.author)" class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                    {{ authorWithCity(item.author) }}
                                </p>
                            </div>

                            <div
                                v-if="canManageNews"
                                class="flex gap-1 self-start opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100 md:justify-self-end"
                            >
                                <Button size="icon" variant="ghost" class="h-8 w-8" @click="openNewsEdit(item)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button size="icon" variant="ghost" class="h-8 w-8" @click="deleteNews(item.id)">
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </article>

                        <div v-if="!newsItems.length" class="py-14 text-sm text-slate-500 dark:text-slate-400">Noch keine Neuigkeiten vorhanden.</div>
                    </section>

                    <section v-if="activeTab === 'suggestions'" aria-label="Vorschläge">
                        <article
                            v-for="s in suggestions"
                            :id="`suggestion-${s.id}`"
                            :key="s.id"
                            class="group scroll-mt-24 border-b border-slate-200 py-5 dark:border-slate-800"
                        >
                            <div class="grid gap-4 md:grid-cols-[4.5rem_1fr_auto]">
                                <time
                                    class="text-xs leading-5"
                                    :class="isNewItem(s) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-500'"
                                >
                                    <span class="block">{{ datePartsFor(s).top }}</span>
                                    <span class="block">{{ datePartsFor(s).bottom }}</span>
                                </time>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2
                                            class="text-[17px] leading-6 font-bold"
                                            :class="isNewItem(s) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-950 dark:text-white'"
                                        >
                                            Vorschlag
                                        </h2>
                                        <Badge
                                            v-if="isNewItem(s)"
                                            variant="outline"
                                            class="rounded-md border-emerald-200 bg-emerald-600 px-2 py-0 text-[11px] text-white dark:border-emerald-700 dark:bg-emerald-500 dark:text-emerald-950"
                                            :title="newUntilLabel(s)"
                                        >
                                            Neu
                                        </Badge>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ s.content }}</p>
                                    <p v-if="authorWithCity(s.author)" class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                        {{ authorWithCity(s.author) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2 self-start md:justify-self-end">
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
                                    >
                                        <ThumbsUp class="mr-1 h-4 w-4" />Like{{ voteCount(s, 'like') ? ` ${voteCount(s, 'like')}` : '' }}
                                    </Button>
                                    <Button
                                        size="sm"
                                        :variant="myVote(s) === 'dislike' ? 'destructive' : 'outline'"
                                        :title="
                                            canVoteOn(s)
                                                ? voteNames(s, 'dislike') || 'Noch keine Dislikes'
                                                : 'Eigene Vorschläge können nicht bewertet werden'
                                        "
                                        :disabled="!canVoteOn(s)"
                                        @click="submitVote(s.id, 'dislike', s)"
                                    >
                                        <ThumbsDown class="mr-1 h-4 w-4" />Dislike{{ voteCount(s, 'dislike') ? ` ${voteCount(s, 'dislike')}` : '' }}
                                    </Button>
                                    <Button
                                        v-if="s.created_by === pageUser.id"
                                        size="icon"
                                        variant="ghost"
                                        class="h-8 w-8"
                                        @click="deleteSuggestion(s.id)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                    <Button v-if="canManageTodos" size="sm" @click="promoteSuggestion(s.id)">In Aufgaben übernehmen</Button>
                                </div>
                            </div>

                            <div v-if="showDislikeCommentInput[s.id]" class="mt-3 space-y-2 md:ml-[6.5rem]">
                                <Textarea
                                    v-model="dislikeCommentBySuggestion[s.id]"
                                    placeholder="Warum gefällt Ihnen die Idee nicht?"
                                    class="text-sm"
                                    @keydown.enter.prevent="submitDislikeComment(s.id)"
                                />
                                <div class="flex justify-end gap-2">
                                    <Button size="sm" variant="ghost" @click="showDislikeCommentInput[s.id] = false">Abbrechen</Button>
                                    <Button size="sm" variant="secondary" @click="submitDislikeComment(s.id)">Kommentar speichern</Button>
                                </div>
                            </div>

                            <div
                                v-if="votesFor(s, 'dislike').length"
                                class="mt-3 space-y-2 rounded-md bg-slate-100 p-3 md:ml-[6.5rem] dark:bg-slate-900"
                            >
                                <div
                                    v-for="v in votesFor(s, 'dislike').filter((x: any) => x.comment)"
                                    :key="`comment-${v.id}`"
                                    class="group/comment relative"
                                >
                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ authorWithCity(v.user) }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ v.comment }}</p>
                                    <Button
                                        v-if="v.user_id === pageUser.id"
                                        size="icon"
                                        variant="ghost"
                                        class="absolute top-0 right-0 h-6 w-6 opacity-0 transition-opacity group-hover/comment:opacity-100"
                                        @click="
                                            () => {
                                                showDislikeCommentInput[s.id] = true;
                                                dislikeCommentBySuggestion[s.id] = v.comment;
                                            }
                                        "
                                    >
                                        <Pencil class="h-3 w-3" />
                                    </Button>
                                    <p class="mt-1 text-right text-[11px] text-slate-400">
                                        <span v-if="v.updated_at && v.updated_at !== v.created_at">bearbeitet am </span
                                        >{{ formatter.format(new Date(v.updated_at || v.created_at)) }}
                                    </p>
                                </div>
                            </div>
                        </article>

                        <div v-if="!suggestions.length" class="py-14 text-sm text-slate-500 dark:text-slate-400">
                            Noch keine Vorschläge vorhanden.
                        </div>
                    </section>

                    <section v-if="activeTab === 'todos'" aria-label="Todos">
                        <article
                            v-for="todo in todos"
                            :key="todo.id"
                            class="group grid gap-4 border-b border-slate-200 py-5 md:grid-cols-[4.5rem_1fr_auto] dark:border-slate-800"
                        >
                            <time
                                class="text-xs leading-5"
                                :class="isNewItem(todo) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-500'"
                            >
                                <span class="block">{{ datePartsFor(todo).top }}</span>
                                <span class="block">{{ datePartsFor(todo).bottom }}</span>
                            </time>

                            <div class="flex min-w-0 items-start gap-3">
                                <Button
                                    v-if="canManageTodos"
                                    size="icon"
                                    variant="outline"
                                    class="mt-1 h-8 w-8 shrink-0 rounded-md"
                                    :aria-label="todo.is_completed ? 'Todo als aktiv markieren' : 'Todo als erledigt markieren'"
                                    :title="todo.is_completed ? 'Als aktiv markieren' : 'Als erledigt markieren'"
                                    @click="toggleTodoCompleted(todo)"
                                >
                                    <Check v-if="todo.is_completed" class="h-4 w-4" />
                                    <Circle v-else class="h-4 w-4" />
                                </Button>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2
                                            class="text-[17px] leading-6 font-bold"
                                            :class="[
                                                isNewItem(todo) ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-950 dark:text-white',
                                                todo.is_completed ? 'text-slate-500 line-through dark:text-slate-500' : '',
                                            ]"
                                        >
                                            {{ todo.task }}
                                        </h2>
                                        <Badge
                                            v-if="isNewItem(todo)"
                                            variant="outline"
                                            class="rounded-md border-emerald-200 bg-emerald-600 px-2 py-0 text-[11px] text-white dark:border-emerald-700 dark:bg-emerald-500 dark:text-emerald-950"
                                            :title="newUntilLabel(todo)"
                                        >
                                            Neu
                                        </Badge>
                                        <Badge :variant="todo.is_completed ? 'secondary' : 'outline'" class="h-5 px-2 text-[11px]">
                                            {{ todo.is_completed ? 'Erledigt' : 'Aktiv' }}
                                        </Badge>
                                    </div>
                                    <p v-if="authorWithCity(todo.author)" class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                        {{ authorWithCity(todo.author) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="canManageTodos"
                                class="flex gap-1 self-start opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100 md:justify-self-end"
                            >
                                <Button size="icon" variant="ghost" class="h-8 w-8" @click="openTodoEdit(todo)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button size="icon" variant="ghost" class="h-8 w-8" @click="deleteTodo(todo.id)">
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </article>

                        <div v-if="!todos.length" class="py-14 text-sm text-slate-500 dark:text-slate-400">Noch keine Todos vorhanden.</div>
                    </section>
                </div>

                <p class="mt-8 text-xs text-slate-400 dark:text-slate-500">{{ activeItemsCount }} Einträge in dieser Ansicht</p>
            </div>
        </main>

        <Dialog
            :open="editingNewsId !== null"
            @update:open="
                (open) => {
                    if (!open) editingNewsId = null;
                }
            "
        >
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>News bearbeiten</DialogTitle>
                </DialogHeader>
                <Input v-model="editNewsForm.title" />
                <RichTextEditor v-model="editNewsForm.content" placeholder="Update schreiben..." />
                <DialogFooter>
                    <Button @click="updateNews">Änderungen speichern</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="editingTodoId !== null"
            @update:open="
                (open) => {
                    if (!open) editingTodoId = null;
                }
            "
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Todo bearbeiten</DialogTitle>
                </DialogHeader>
                <Textarea v-model="editTodoForm.task" />
                <DialogFooter>
                    <Button @click="updateTodoText">Änderungen speichern</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.announcement-copy {
    max-width: 46rem;
}

.announcement-copy :deep(p),
.announcement-copy :deep(div) {
    margin: 0;
}

.announcement-copy :deep(p + p),
.announcement-copy :deep(div + div) {
    margin-top: 0.35rem;
}

.announcement-copy :deep(ul) {
    list-style: disc;
    margin: 0.35rem 0 0.35rem 1.25rem;
}

.announcement-copy :deep(ol) {
    list-style: decimal;
    margin: 0.35rem 0 0.35rem 1.25rem;
}

.announcement-copy :deep(a) {
    color: rgb(37 99 235);
    font-weight: 500;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.announcement-copy :deep(blockquote) {
    border-left: 3px solid rgb(148 163 184);
    color: rgb(71 85 105);
    margin: 0.75rem 0;
    padding-left: 1rem;
}

.announcement-copy :deep(h3),
.announcement-copy :deep(h4) {
    color: rgb(15 23 42);
    font-weight: 700;
    margin-top: 0.75rem;
}

.dark .announcement-copy :deep(blockquote) {
    color: rgb(203 213 225);
}

.dark .announcement-copy :deep(h3),
.dark .announcement-copy :deep(h4) {
    color: rgb(248 250 252);
}
</style>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Checkbox } from '@/components/ui/checkbox';
import { ThumbsUp, ThumbsDown, Trash2, CheckCircle2, MessageSquare, Plus, EyeOff, ArrowRight } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    firstname: string;
    role: string;
}

interface Vote {
    id: number;
    user_id: number;
    vote: boolean;
    comment: string | null;
    user: {
        name: string;
        firstname: string;
    };
}

interface NewsItem {
    id: number;
    title: string;
    content: string;
    created_at: string;
    user: {
        name: string;
        firstname: string;
    };
}

interface TodoItem {
    id: number;
    description: string;
    is_completed: boolean;
    created_at: string;
}

interface SuggestionItem {
    id: number;
    content: string;
    user_id: number;
    created_at: string;
    user: {
        name: string;
        firstname: string;
    };
    votes: Vote[];
}

defineProps<{
    news: NewsItem[];
    todos: TodoItem[];
    suggestions: SuggestionItem[];
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any).user as User);
const isAdmin = computed(() => user.value.role === 'admin');

// Forms
const newsForm = useForm({
    title: '',
    content: '',
});

const todoForm = useForm({
    description: '',
});

const suggestionForm = useForm({
    content: '',
});

const voteForm = useForm({
    vote: true,
    comment: '',
});

const activeVoteId = ref<number | null>(null);

// Actions
const submitNews = () => {
    newsForm.post(route('collaboration.news.store'), {
        onSuccess: () => newsForm.reset(),
    });
};

const deleteNews = (id: number) => {
    if (confirm('Möchten Sie diese Nachricht wirklich löschen?')) {
        useForm({}).delete(route('collaboration.news.destroy', id));
    }
};

const submitTodo = () => {
    todoForm.post(route('collaboration.todos.store'), {
        onSuccess: () => todoForm.reset(),
    });
};

const toggleTodo = (todo: TodoItem) => {
    useForm({
        is_completed: !todo.is_completed,
    }).patch(route('collaboration.todos.update', todo.id));
};

const deleteTodo = (id: number) => {
    if (confirm('Möchten Sie dieses Todo wirklich löschen?')) {
        useForm({}).delete(route('collaboration.todos.destroy', id));
    }
};

const submitSuggestion = () => {
    suggestionForm.post(route('collaboration.suggestions.store'), {
        onSuccess: () => suggestionForm.reset(),
    });
};

const hideSuggestion = (id: number) => {
    if (confirm('Möchten Sie diesen Vorschlag wirklich ausblenden?')) {
        useForm({}).patch(route('collaboration.suggestions.hide', id));
    }
};

const openVoteDialog = (suggestionId: number, isLike: boolean) => {
    activeVoteId.value = suggestionId;
    voteForm.vote = isLike;
    voteForm.comment = '';

    // If it's a like, we can submit immediately or ask for comment.
    // The requirement says: "if the voting is bad, then there should be a section to add a comment why voted bad."
    if (isLike) {
        submitVote();
    }
};

const submitVote = () => {
    if (activeVoteId.value) {
        voteForm.post(route('collaboration.suggestions.vote', activeVoteId.value), {
            onSuccess: () => {
                activeVoteId.value = null;
                voteForm.reset();
            }
        });
    }
};

const removeVote = (suggestionId: number) => {
    useForm({}).delete(route('collaboration.suggestions.vote.remove', suggestionId));
};

const promoteSuggestion = (suggestionId: number) => {
    if (confirm('Möchten Sie diesen Vorschlag in die Aufgaben übernehmen?')) {
        useForm({}).post(route('collaboration.suggestions.promote', suggestionId));
    }
};

const getVotesCount = (votes: Vote[], isLike: boolean) => {
    return votes.filter(v => v.vote === isLike).length;
};

const hasVoted = (votes: Vote[], isLike: boolean) => {
    return votes.some(v => v.user_id === user.value.id && v.vote === isLike);
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Kollaboration', href: '/collaboration' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Kollaboration" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-8 p-6 max-w-7xl mx-auto w-full">

            <!-- News Area -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <MessageSquare class="w-6 h-6 text-blue-500" />
                        Neuigkeiten & Updates
                    </h2>
                </div>

                <div v-if="isAdmin" class="mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Neue Nachricht verfassen</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitNews" class="space-y-4">
                                <Input v-model="newsForm.title" placeholder="Titel" required />
                                <Textarea v-model="newsForm.content" placeholder="Inhalt..." required />
                                <Button type="submit" :disabled="newsForm.processing">
                                    <Plus class="w-4 h-4 mr-2" /> Nachricht posten
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <Card v-for="item in news" :key="item.id" class="flex flex-col">
                        <CardHeader class="flex flex-row justify-between items-start space-y-0">
                            <div>
                                <CardTitle>{{ item.title }}</CardTitle>
                                <CardDescription>
                                    Von {{ item.user.firstname }} {{ item.user.name }} am {{ formatDate(item.created_at) }}
                                </CardDescription>
                            </div>
                            <Button v-if="isAdmin" variant="ghost" size="icon" @click="deleteNews(item.id)" class="text-red-500">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </CardHeader>
                        <CardContent class="flex-1">
                            <p class="whitespace-pre-wrap text-sm text-gray-700">{{ item.content }}</p>
                        </CardContent>
                    </Card>
                    <div v-if="news.length === 0" class="col-span-full py-12 text-center text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed">
                        Keine Neuigkeiten vorhanden.
                    </div>
                </div>
            </section>

            <Separator />

            <!-- Todos Area -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <CheckCircle2 class="w-6 h-6 text-green-500" />
                        Aufgaben (Todos)
                    </h2>
                </div>

                <div v-if="isAdmin" class="mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Neue Aufgabe hinzufügen</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitTodo" class="flex gap-2">
                                <Input v-model="todoForm.description" placeholder="Aufgabenbeschreibung..." required />
                                <Button type="submit" :disabled="todoForm.processing">
                                    <Plus class="w-4 h-4 mr-2" /> Hinzufügen
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent class="p-0">
                        <div class="divide-y">
                            <div v-for="todo in todos" :key="todo.id" class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        :checked="todo.is_completed"
                                        :disabled="!isAdmin"
                                        @update:checked="toggleTodo(todo)"
                                    />
                                    <span :class="['text-sm', todo.is_completed ? 'line-through text-gray-400' : 'text-gray-900']">
                                        {{ todo.description }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge v-if="todo.is_completed" variant="secondary" class="bg-green-100 text-green-700">Erledigt</Badge>
                                    <Button v-if="isAdmin" variant="ghost" size="icon" @click="deleteTodo(todo.id)" class="text-red-500">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                            <div v-if="todos.length === 0" class="p-8 text-center text-gray-500">
                                Keine Aufgaben vorhanden.
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <Separator />

            <!-- Suggestions Area -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <Plus class="w-6 h-6 text-purple-500" />
                        Vorschläge & Ideen
                    </h2>
                </div>

                <div class="mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Einen Vorschlag einreichen</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitSuggestion" class="space-y-4">
                                <Textarea v-model="suggestionForm.content" placeholder="Was ist deine Idee?" required />
                                <Button type="submit" variant="secondary" :disabled="suggestionForm.processing">
                                    Vorschlag senden
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6">
                    <Card v-for="suggestion in suggestions" :key="suggestion.id">
                        <CardHeader class="pb-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <CardDescription>
                                        Eingereicht von {{ suggestion.user.firstname }} {{ suggestion.user.name }}
                                    </CardDescription>
                                </div>
                                <div class="flex gap-2">
                                    <Button v-if="isAdmin" variant="outline" size="sm" @click="promoteSuggestion(suggestion.id)" class="text-green-600 border-green-200 hover:bg-green-50">
                                        <ArrowRight class="w-4 h-4 mr-1" /> In Aufgaben übernehmen
                                    </Button>
                                    <Button v-if="isAdmin" variant="ghost" size="icon" @click="hideSuggestion(suggestion.id)" class="text-gray-400 hover:text-gray-600" title="Ausblenden">
                                        <EyeOff class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-lg font-medium">{{ suggestion.content }}</p>

                            <div class="mt-4 flex items-center gap-4">
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :class="['px-2', hasVoted(suggestion.votes, true) ? 'text-blue-600 bg-blue-50' : '']"
                                        @click="hasVoted(suggestion.votes, true) ? removeVote(suggestion.id) : openVoteDialog(suggestion.id, true)"
                                    >
                                        <ThumbsUp class="w-4 h-4 mr-2" />
                                        {{ getVotesCount(suggestion.votes, true) }}
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        :class="['px-2', hasVoted(suggestion.votes, false) ? 'text-red-600 bg-red-50' : '']"
                                        @click="hasVoted(suggestion.votes, false) ? removeVote(suggestion.id) : (activeVoteId = suggestion.id, voteForm.vote = false)"
                                    >
                                        <ThumbsDown class="w-4 h-4 mr-2" />
                                        {{ getVotesCount(suggestion.votes, false) }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Negative Vote Comment Form -->
                            <div v-if="activeVoteId === suggestion.id && !voteForm.vote" class="mt-4 p-4 bg-gray-50 rounded-lg border">
                                <p class="text-sm font-medium mb-2">Warum gefällt dir dieser Vorschlag nicht?</p>
                                <Textarea v-model="voteForm.comment" placeholder="Dein Feedback..." class="mb-2 bg-white" />
                                <div class="flex gap-2">
                                    <Button size="sm" @click="submitVote" :disabled="voteForm.processing">Abstimmen</Button>
                                    <Button size="sm" variant="ghost" @click="activeVoteId = null">Abbrechen</Button>
                                </div>
                            </div>

                            <!-- Comments/Votes display -->
                            <div v-if="suggestion.votes.some(v => v.comment)" class="mt-4 space-y-2">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Feedback:</p>
                                <div v-for="vote in suggestion.votes.filter(v => v.comment)" :key="vote.id" class="text-sm bg-gray-50 p-2 rounded">
                                    <span class="font-semibold">{{ vote.user.firstname }} {{ vote.user.name }}:</span>
                                    <span class="text-gray-600 ml-1 italic">"{{ vote.comment }}"</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <div v-if="suggestions.length === 0" class="py-12 text-center text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed">
                        Noch keine Vorschläge eingereicht.
                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>

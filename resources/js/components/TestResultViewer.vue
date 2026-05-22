<script setup lang="ts">
defineOptions({
    inheritAttrs: false,
});
import AvemResult from '@/components/AvemResult.vue';
import BIT2Result from '@/components/BIT2Result.vue';
import BtResult from '@/components/BtResult.vue';
import KonzentrationstestResult from '@/components/KonzentrationstestResult.vue';
import LmtResult from '@/components/LmtResult.vue';
import LpsResult from '@/components/LpsResult.vue';
import MrtAResult from '@/components/MrtAResult.vue';
import MrtBResult from '@/components/MrtBResult.vue';
import { Input } from '@/components/ui/input';
import { FPI_QUESTIONS } from '@/pages/Questions/FPIQuestions';
import FpiResult from '@/pages/Scores/FPI/FPIResult.vue';
import { norms_female_16_24 } from '@/pages/Scores/FPI/norms_female_16_24';
import { norms_female_25_44 } from '@/pages/Scores/FPI/norms_female_25_44';
import { norms_female_45_59 } from '@/pages/Scores/FPI/norms_female_45_59';
import { norms_female_60up } from '@/pages/Scores/FPI/norms_female_60up';
import { norms_male_16_24 } from '@/pages/Scores/FPI/norms_male_16_24';
import { norms_male_25_44 } from '@/pages/Scores/FPI/norms_male_25_44';
import { norms_male_45_59 } from '@/pages/Scores/FPI/norms_male_45_59';
import { norms_male_60up } from '@/pages/Scores/FPI/norms_male_60up';
import { computed, ref, watch } from 'vue';

interface Answer {
    question: string;
    user_answer: string;
    correct_answers?: string[] | string;
    time_seconds?: number;
    is_correct?: boolean;
}

interface ResultJson {
    rohwert?: number;
    prozentrang?: number;
    twert?: number;
    total_time_seconds?: number;
    answers: Answer[];
    [key: string]: any;
}

type FpiAnswerValue = 'stimmt' | 'stimmtNicht';

const props = withDefaults(
    defineProps<{
        modelValue: ResultJson | null;
        test: { name: string; code?: string };
        participantProfile?: { age: number; sex?: string } | null;
        showAnswers?: boolean;
        forceOpenAnswers?: boolean;
        testResultId?: number | null;
        manualScores?: Array<{ key: string; value: number | string | null }> | Record<string, any>;
    }>(),
    {
        showAnswers: true,
    },
);

const emit = defineEmits(['update:modelValue', 'manual-score-updated']);

const local = ref<ResultJson | null>(null);
const localManualScores = ref<Array<{ key: string; value: number | string | null }> | Record<string, any>>(props.manualScores ?? []);
const manualScoreMap = computed<Record<string, number | string | null>>(() => {
    const src: any = localManualScores.value ?? [];
    if (Array.isArray(src)) {
        return src.reduce<Record<string, number | string | null>>((acc, entry) => {
            if (entry && typeof entry.key === 'string') {
                acc[entry.key] = entry.value ?? null;
            }
            return acc;
        }, {});
    }
    if (src && typeof src === 'object') {
        return src as Record<string, number | string | null>;
    }
    return {};
});
const isKonzentrationstest = computed(() => ['Konzentrationstest', '628 Test'].includes((props.test?.name || '').trim()));
const isFpiRTest = computed(() => [props.test?.name, props.test?.code].some((value) => String(value ?? '').trim() === 'FPI-R'));

const fpiStanineKeys = ['LEB', 'SOZ', 'LEI', 'GEH', 'ERR', 'AGGR', 'BEAN', 'KORP', 'GES', 'OFF', 'EXTR', 'EMOT'];
const fpiScoreKeys: (number | string)[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];

watch(
    () => props.modelValue,
    (val) => {
        local.value = val ? JSON.parse(JSON.stringify(val)) : null;
    },
    { immediate: true, deep: true },
);

watch(
    () => props.manualScores,
    (val) => {
        localManualScores.value = (val as any) ?? [];
    },
    { immediate: true, deep: true },
);

watch(
    local,
    (val) => {
        emit('update:modelValue', val as any);
    },
    { deep: true },
);

function formatTime(seconds?: number | null) {
    if (seconds == null) return '–';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}

function handleManualScoreUpdated(payload: { key: string; value: number | null }) {
    const current = localManualScores.value;
    if (Array.isArray(current)) {
        const existing = current.find((entry) => entry.key === payload.key);
        if (existing) {
            existing.value = payload.value;
        } else {
            current.push({ key: payload.key, value: payload.value });
        }
        localManualScores.value = [...current];
    } else if (current && typeof current === 'object') {
        localManualScores.value = { ...current, [payload.key]: payload.value };
    } else {
        localManualScores.value = { [payload.key]: payload.value };
    }
    emit('manual-score-updated', payload);
}

function getFpiNormTable(sex?: string, age?: number) {
    if (!sex || !age) return null;
    const s = sex.toLowerCase();
    const isFemale = s === 'f' || s === 'female' || s === 'w' || s === 'weiblich';
    if (age >= 16 && age <= 24) {
        return isFemale ? norms_female_16_24 : norms_male_16_24;
    }
    if (age >= 25 && age <= 44) {
        return isFemale ? norms_female_25_44 : norms_male_25_44;
    }
    if (age >= 45 && age <= 59) {
        return isFemale ? norms_female_45_59 : norms_male_45_59;
    }
    return isFemale ? norms_female_60up : norms_male_60up;
}

function getFpiNormRanges(table: Record<string, [number, number][]>, key: string) {
    return table[key] ?? (key === 'KORP' ? table['K\u00d6RP'] : undefined) ?? [];
}

function getFpiAnswer(entry: any): FpiAnswerValue | null {
    const value = entry?.answer ?? entry?.user_answer;
    return value === 'stimmt' || value === 'stimmtNicht' ? value : null;
}

function calculateFpiScoreMapFromAnswers(answers: any[] | null | undefined) {
    if (!Array.isArray(answers)) return null;

    const scores = fpiScoreKeys.reduce<Record<string, number>>((acc, key) => {
        acc[String(key)] = 0;
        return acc;
    }, {});

    let hasScorableAnswer = false;

    answers.forEach((entry, index) => {
        const answer = getFpiAnswer(entry);
        const number = entry?.number != null ? Number(entry.number) : FPI_QUESTIONS[index]?.number;
        const question = FPI_QUESTIONS.find((candidate) => candidate.number === number);

        if (!answer || !question) return;

        hasScorableAnswer = true;
        for (const effect of question[answer] ?? []) {
            const key = String(effect.category);
            scores[key] = (scores[key] ?? 0) + effect.points;
        }
    });

    return hasScorableAnswer ? scores : null;
}

function calculateFpiMissingAnswerNumbers(answers: any[] | null | undefined) {
    const answerByNumber = new Map<number, FpiAnswerValue | null>();

    if (Array.isArray(answers)) {
        answers.forEach((entry, index) => {
            const number = entry?.number != null ? Number(entry.number) : FPI_QUESTIONS[index]?.number;
            if (typeof number === 'number' && Number.isFinite(number)) {
                answerByNumber.set(number, getFpiAnswer(entry));
            }
        });
    }

    return FPI_QUESTIONS.filter((question) => !answerByNumber.has(question.number) || !answerByNumber.get(question.number)).map(
        (question) => question.number,
    );
}

function normalizeFpiScoreMap(src: any) {
    const categoryScores = src?.category_scores ?? src?.categoryScores;
    if (categoryScores && typeof categoryScores === 'object' && !Array.isArray(categoryScores)) {
        return categoryScores as Record<string, number | string | null>;
    }

    const rawArray =
        Array.isArray(src?.rohwerte) && src.rohwerte.length
            ? src.rohwerte
            : Array.isArray(src?.categoryScores) && src.categoryScores.length
              ? src.categoryScores
              : null;

    if (rawArray) {
        return fpiScoreKeys.reduce<Record<string, number | string | null>>((acc, key, index) => {
            acc[String(key)] = rawArray[index] ?? null;
            return acc;
        }, {});
    }

    return calculateFpiScoreMapFromAnswers(src?.answers);
}

const fpiMissingAnswerCount = computed(() => {
    if (!local.value) return 0;

    const src: any = local.value;
    const rawCount = src.missing_answer_count ?? src.missingAnswerCount ?? src.unanswered_count ?? src.unansweredCount;
    const count = Number(rawCount);

    if (rawCount != null && Number.isFinite(count)) {
        return count;
    }

    return calculateFpiMissingAnswerNumbers(src.answers).length;
});

const fpiStanines = computed(() => {
    if (!local.value) return [];
    const src: any = local.value;

    // Direct arrays
    const direct = src.stanines || src.categoryStanines;
    if (Array.isArray(direct) && direct.some((v: any) => v != null)) {
        return direct.map((v: any) => (v != null ? Number(v) : null));
    }

    // Object with keys
    const obj = src.category_stanines || src.categoryStanines || src.stanines;
    if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
        const arr = fpiStanineKeys.map((key) => {
            const value = obj[key] ?? (key === 'KORP' ? obj['K\u00d6RP'] : null);
            return value != null ? Number(value) : null;
        });
        if (arr.some((v) => v != null)) return arr;
    }

    // Derive from stored or legacy raw answers.
    const scores = normalizeFpiScoreMap(src);
    if (scores) {
        const table = getFpiNormTable(props.participantProfile?.sex, props.participantProfile?.age);
        if (!table) return [];
        return fpiStanineKeys.map((key, idx) => {
            const raw = Number(scores[String(fpiScoreKeys[idx])] ?? NaN);
            if (!Number.isFinite(raw)) return null;
            const ranges: [number, number][] = getFpiNormRanges(table as any, key);
            for (let i = 0; i < ranges.length; i++) {
                const [min, max] = ranges[i];
                if (raw >= min && raw <= max) return i + 1;
            }
            return null;
        });
    }
    return [];
});

const fpiRohwerte = computed(() => {
    if (!local.value) return [];
    const src: any = local.value;
    if (Array.isArray(src.rohwerte) && src.rohwerte.length) return src.rohwerte;
    if (Array.isArray(src.categoryScores) && src.categoryScores.length) return src.categoryScores;
    const scores = normalizeFpiScoreMap(src);
    if (scores) {
        return fpiScoreKeys.map((key) => scores[String(key)] ?? null);
    }
    return [];
});
</script>

<template>
    <div v-if="local" v-bind="$attrs">
        <MrtAResult v-if="test.name === 'MRT-A'" :results="local" />
        <MrtBResult v-else-if="test.name === 'MRT-B'" :results="local" />
        <KonzentrationstestResult v-else-if="isKonzentrationstest" :results="local" :show-answers="showAnswers" />
        <LmtResult v-else-if="test.name === 'LMT'" :results="local" :show-answers="showAnswers" />
        <FpiResult
            v-else-if="isFpiRTest"
            :stanines="fpiStanines"
            :rohwerte="fpiRohwerte"
            :answers="local.answers"
            :missing-answer-count="fpiMissingAnswerCount"
            :show-answers="showAnswers"
        />
        <BIT2Result v-else-if="test.name === 'BIT-2'" :results="local" :participantProfile="participantProfile" :show-answers="showAnswers" />
        <LpsResult
            v-else-if="['LPS', 'LPS-A', 'LPS-B'].includes(test.name)"
            :results="local"
            :test-name="test.name"
            :participant-profile="participantProfile"
            :test-result-id="testResultId"
            :manual-scores="manualScoreMap"
            @manual-score-updated="handleManualScoreUpdated"
        />
        <BtResult
            v-else-if="test.name === 'BT'"
            :test-result-id="testResultId"
            :manual-scores="manualScoreMap"
            :results="local"
            :show-answers="showAnswers"
            :force-open-answers="forceOpenAnswers"
            @manual-score-updated="handleManualScoreUpdated"
        />
        <AvemResult v-else-if="test.name === 'AVEM'" :results="local" :show-answers="showAnswers" />
        <template v-else>
            <table class="mb-4 w-full overflow-hidden rounded-lg border text-sm shadow">
                <tbody>
                    <tr class="bg-muted/40 dark:bg-gray-700">
                        <td class="w-1/2 px-3 py-2 font-semibold">Rohwert</td>
                        <td class="px-3 py-2">{{ local.rohwert }}</td>
                    </tr>
                    <tr class="dark:bg-gray-800">
                        <td class="px-3 py-2 font-semibold">Prozentrang (PR)</td>
                        <td class="px-3 py-2">{{ local.prozentrang }}</td>
                    </tr>
                    <tr class="bg-muted/40 dark:bg-gray-700">
                        <td class="px-3 py-2 font-semibold">T-Wert (Normwert)</td>
                        <td class="px-3 py-2">{{ local.twert }}</td>
                    </tr>
                    <tr class="dark:bg-gray-800">
                        <td class="px-3 py-2 font-semibold">Benötigte Zeit</td>
                        <td class="px-3 py-2">{{ formatTime(local.total_time_seconds) }}</td>
                    </tr>
                </tbody>
            </table>

            <template v-if="test.name === 'BRT-A' || test.name === 'BRT-B'">
                <h3 class="mb-2 font-bold">Antworten</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full rounded-lg border text-sm shadow">
                        <thead class="bg-muted/40 dark:bg-gray-700">
                            <tr>
                                <th class="px-2 py-1 text-left font-semibold">#</th>
                                <th class="px-2 py-1 text-left font-semibold">Frage</th>
                                <th class="px-2 py-1 text-left font-semibold">Ihre Antwort</th>
                                <th class="px-2 py-1 text-left font-semibold">Richtige Antwort</th>
                                <th class="px-2 py-1 text-left font-semibold">Bearbeitungszeit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(ans, idx) in local.answers"
                                :key="idx"
                                :class="ans.is_correct ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'"
                            >
                                <td class="px-2 py-1 font-medium text-muted-foreground">{{ idx + 1 }}</td>
                                <td class="px-2 py-1 align-top">{{ ans.question }}</td>
                                <td class="px-2 py-1">
                                    <Input v-model="ans.user_answer" class="w-24" />
                                </td>
                                <td class="px-2 py-1">
                                    <span class="font-mono">{{
                                        Array.isArray(ans.correct_answers) ? ans.correct_answers.join(', ') : ans.correct_answers
                                    }}</span>
                                </td>
                                <td class="min-w-[60px] px-2 py-1 text-right font-mono">{{ formatTime(ans.time_seconds) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </template>
    </div>
</template>

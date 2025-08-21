<script setup lang="ts">
defineOptions({
    inheritAttrs: false,
});
import MrtAResult from '@/components/MrtAResult.vue';
import LmtResult from '@/components/LmtResult.vue';
import FpiResult from '@/pages/Scores/FPI/FPIResult.vue';
import { norms_male_16_24 } from '@/pages/Scores/FPI/norms_male_16_24';
import { norms_male_25_44 } from '@/pages/Scores/FPI/norms_male_25_44';
import { norms_male_45_59 } from '@/pages/Scores/FPI/norms_male_45_59';
import { norms_male_60up } from '@/pages/Scores/FPI/norms_male_60up';
import { norms_female_16_24 } from '@/pages/Scores/FPI/norms_female_16_24';
import { norms_female_25_44 } from '@/pages/Scores/FPI/norms_female_25_44';
import { norms_female_45_59 } from '@/pages/Scores/FPI/norms_female_45_59';
import { norms_female_60up } from '@/pages/Scores/FPI/norms_female_60up';
import { Input } from '@/components/ui/input';
import { computed, ref, watch } from 'vue';
const bit2Groups = ['TH', 'GH', 'TN', 'EH', 'LF', 'KB', 'VB', 'LG', 'SE'];

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

const props = defineProps<{
    modelValue: ResultJson | null;
    test: { name: string };
    participantProfile?: { age: number; sex?: string } | null;
}>();

const emit = defineEmits(['update:modelValue']);

const local = ref<ResultJson | null>(null);

watch(
    () => props.modelValue,
    (val) => {
        local.value = val ? JSON.parse(JSON.stringify(val)) : null;
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

// --- BIT‑2 percentile table (real values) ---
interface NormRow {
    percentile: string;
    [key: string]: string | number | null;
}

const bit2NormTables: Record<'m' | 'f', NormRow[]> = {
    m: [
        { percentile: '100', TH: 41, GH: 44, TN: 45, EH: 44, LF: 45, KB: 43, VB: 43, LG: 44, SE: 45 },
        { percentile: '95-', TH: 35, GH: 38, TN: 42, EH: 37, LF: 41, KB: 36, VB: 32, LG: 37, SE: 40 },
        { percentile: '90-', TH: 32, GH: 35, TN: 40, EH: 35, LF: 38, KB: 33, VB: 30, LG: 34, SE: 36 },
        { percentile: '85-', TH: 31, GH: 33, TN: 38, EH: 33, LF: 36, KB: 31, VB: 28, LG: 32, SE: 35 },
        { percentile: '80-', TH: 30, GH: 32, TN: 37, EH: 32, LF: 34, KB: 29, VB: 27, LG: 29, SE: 33 },
        { percentile: '75-', TH: 29, GH: 31, TN: 36, EH: 31, LF: 32, KB: 28, VB: 26, LG: 27, SE: 31 },
        { percentile: '70-', TH: 28, GH: 28, TN: 35, EH: 28, LF: 31, KB: 27, VB: 25, LG: 26, SE: 30 },
        { percentile: '65-', TH: 26, GH: 27, TN: 34, EH: 27, LF: 29, KB: 26, VB: 24, LG: 24, SE: 28 },
        { percentile: '60-', TH: 25, GH: 26, TN: 32, EH: 26, LF: 28, KB: 25, VB: 23, LG: 23, SE: 27 },
        { percentile: '55-', TH: 24, GH: 25, TN: 31, EH: 25, LF: 27, KB: 24, VB: 22, LG: null, SE: 26 },
        { percentile: '50-', TH: 23, GH: 23, TN: 29, EH: null, LF: 26, KB: 23, VB: null, LG: 22, SE: 24 },
        { percentile: '45-', TH: 21, GH: 22, TN: 28, EH: 24, LF: 25, KB: 22, VB: 21, LG: 20, SE: 23 },
        { percentile: '40-', TH: 20, GH: 21, TN: 27, EH: 23, LF: 23, KB: 21, VB: 19, LG: null, SE: 21 },
        { percentile: '35-', TH: 19, GH: 20, TN: 25, EH: 21, LF: 22, KB: 20, VB: 18, LG: 19, SE: 20 },
        { percentile: '30-', TH: 18, GH: 19, TN: 23, EH: 20, LF: 20, KB: 19, VB: 16, LG: 18, SE: 19 },
        { percentile: '25-', TH: 16, GH: 18, TN: 21, EH: 19, LF: 19, KB: 17, VB: 15, LG: 17, SE: 18 },
        { percentile: '20-', TH: 14, GH: 17, TN: 19, EH: 18, LF: 18, KB: 16, VB: 14, LG: 16, SE: 17 },
        { percentile: '15-', TH: 13, GH: 15, TN: 18, EH: 17, LF: 17, KB: 15, VB: 12, LG: 15, SE: 15 },
        { percentile: '10-', TH: 10, GH: 14, TN: 16, EH: 15, LF: 15, KB: 13, VB: 11, LG: 14, SE: 14 },
        { percentile: '5-', TH: 9, GH: 13, TN: 13, EH: 13, LF: 13, KB: 11, VB: 9, LG: 11, SE: 12 },
        { percentile: '>0-', TH: null, GH: 9, TN: 9, EH: 9, LF: 9, KB: 9, VB: null, LG: 9, SE: 9 },
    ],
    f: [
        { percentile: '100', TH: 43, GH: 45, TN: 45, EH: 44, LF: 45, KB: 42, VB: 45, LG: 44, SE: 45 },
        { percentile: '95', TH: 27, GH: 41, TN: 36, EH: 38, LF: 39, KB: 34, VB: 34, LG: 37, SE: 42 },
        { percentile: '90', TH: 24, GH: 38, TN: 34, EH: 35, LF: 36, KB: 31, VB: 31, LG: 35, SE: 41 },
        { percentile: '85', TH: 22, GH: 37, TN: 31, EH: 34, LF: 34, KB: 29, VB: 29, LG: 32, SE: 39 },
        { percentile: '80', TH: 20, GH: 36, TN: 28, EH: 32, LF: 32, KB: 28, VB: 27, LG: 31, SE: 38 },
        { percentile: '75', TH: null, GH: 35, TN: 26, EH: 30, LF: 31, KB: 27, VB: 26, LG: 29, SE: 37 },
        { percentile: '70', TH: 19, GH: 33, TN: 25, EH: 29, LF: 29, KB: 26, VB: 25, LG: 27, SE: 36 },
        { percentile: '65', TH: 18, GH: 32, TN: 24, EH: 28, LF: 28, KB: 25, VB: null, LG: 26, SE: null },
        { percentile: '60', TH: 17, GH: 31, TN: 23, EH: 27, LF: 27, KB: 24, VB: 24, LG: 25, SE: 35 },
        { percentile: '55', TH: 16, GH: 29, TN: 22, EH: 26, LF: 26, KB: null, VB: 23, LG: 24, SE: 34 },
        { percentile: '50', TH: 15, GH: 28, TN: 21, EH: null, LF: 25, KB: 23, VB: 22, LG: 23, SE: 33 },
        { percentile: '45', TH: 14, GH: 27, TN: 20, EH: 25, LF: 24, KB: null, VB: 21, LG: 22, SE: 32 },
        { percentile: '40', TH: 13, GH: null, TN: 19, EH: 24, LF: 23, KB: 22, VB: 20, LG: 21, SE: 31 },
        { percentile: '35', TH: 12, GH: 26, TN: 18, EH: 23, LF: 22, KB: 21, VB: 19, LG: 20, SE: 30 },
        { percentile: '30', TH: null, GH: 24, TN: 17, EH: 22, LF: 21, KB: 20, VB: 18, LG: 18, SE: 29 },
        { percentile: '25', TH: 11, GH: 23, TN: 16, EH: 21, LF: 20, KB: 19, VB: 16, LG: 17, SE: 27 },
        { percentile: '20', TH: 10, GH: 21, TN: 15, EH: 20, LF: 18, KB: 18, VB: 15, LG: 16, SE: 26 },
        { percentile: '15', TH: 9, GH: 20, TN: 14, EH: 19, LF: 17, KB: 17, VB: 13, LG: 15, SE: 24 },
        { percentile: '10', TH: null, GH: 17, TN: 12, EH: 17, LF: 16, KB: 15, VB: 11, LG: 14, SE: 22 },
        { percentile: '5', TH: null, GH: 15, TN: 11, EH: 14, LF: 14, KB: 13, VB: 9, LG: 12, SE: 18 },
        { percentile: '>0', TH: null, GH: 9, TN: 9, EH: 9, LF: 9, KB: 9, VB: null, LG: 9, SE: 10 },
    ],
};

const normTable = computed(() => bit2NormTables[props.participantProfile?.sex === 'f' ? 'f' : 'm']);

const highlighted = computed(() => {
    const map: Record<string, number | null> = {};
    if (!local.value) return map;
    for (const code of bit2Groups) {
        const raw = local.value.group_totals?.[code] ?? 0;
        let idx: number | null = null;
        const rows = normTable.value;
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const threshold = row[code] as number | null;
            if (threshold != null && raw >= threshold) {
                idx = i;
                break;
            }
        }
        map[code] = idx;
    }
    return map;
});

const fpiStanines = computed(() => {
    if (!local.value) return [];
    const stanineKeys = ['LEB', 'SOZ', 'LEI', 'GEH', 'ERR', 'AGGR', 'BEAN', 'KORP', 'GES', 'OFF', 'EXTR', 'EMOT'];
    const scoreKeys: (number | string)[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];
    const src: any = local.value;

    // Direct arrays
    const direct = src.stanines || src.categoryStanines;
    if (Array.isArray(direct) && direct.some((v: any) => v != null)) {
        return direct.map((v: any) => (v != null ? Number(v) : null));
    }

    // Object with keys
    const obj = src.category_stanines || src.categoryStanines || src.stanines;
    if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
        const arr = stanineKeys.map((k) => (obj[k] != null ? Number(obj[k]) : null));
        if (arr.some((v) => v != null)) return arr;
    }

    // Derive from raw scores
    const scores = src.category_scores || src.categoryScores;
    if (scores) {
        const table = getFpiNormTable(props.participantProfile?.sex, props.participantProfile?.age);
        if (!table) return [];
        return stanineKeys.map((key, idx) => {
            const raw = scores[scoreKeys[idx]];
            if (raw == null) return null;
            const ranges: [number, number][] = (table as any)[key] ?? [];
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
    if (Array.isArray(src.rohwerte)) return src.rohwerte;
    if (Array.isArray(src.categoryScores)) return src.categoryScores;
    const scores = src.category_scores || src.categoryScores;
    if (scores) {
        const keys: (string | number)[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'E', 'N'];
        return keys.map((k) => scores[k] ?? null);
    }
    return [];
});
</script>

<template>
    <div v-if="local" v-bind="$attrs">
        <MrtAResult v-if="test.name === 'MRT-A'" :results="local" />
        <LmtResult v-else-if="test.name === 'LMT'" :results="local" />
        <FpiResult v-else-if="test.name === 'FPI-R'" :stanines="fpiStanines" :rohwerte="fpiRohwerte" />
        <div v-else-if="test.name === 'BIT-2'" class="overflow-x-auto">
            <table class="mb-4 w-full overflow-hidden rounded-lg border text-sm shadow">
                <thead class="bg-muted/40 dark:bg-gray-700">
                    <tr>
                        <th v-for="(code, idx) in bit2Groups" :key="code" class="px-3 py-2 text-center font-semibold">{{ idx + 1 }} {{ code }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dark:bg-gray-800">
                        <td v-for="code in bit2Groups" :key="code" class="px-3 py-2 text-center">
                            {{ local.group_totals?.[code] ?? 0 }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Norm table -->
            <table class="min-w-full border text-xs">
                <thead class="bg-muted/40 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-1 text-center font-semibold">{{ props.participantProfile?.sex === 'f' ? '♀' : '♂' }}</th>
                        <th v-for="code in bit2Groups" :key="code" class="px-2 py-1 font-semibold">
                            {{ code }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, rIdx) in normTable"
                        :key="rIdx"
                        :class="{
                            'border-b-2 border-blue-500': ['85-', '20-'].includes(row.percentile as string),
                        }"
                    >
                        <td class="px-2 py-1 text-center">{{ row.percentile }}</td>
                        <td
                            v-for="code in bit2Groups"
                            :key="code"
                            class="px-2 py-1 text-center"
                            :class="{
                                'text-red-600 underline decoration-2': highlighted[code] === rIdx,
                            }"
                        >
                            {{ row[code] }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
    </div>
</template>

<script setup lang="ts">
import { buildEntranceAnalysis, type ObservationFields } from '@/lib/entrance-analysis';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        participant: any;
        assignments: any[];
        teacherName?: string;
        conductedAt?: string | null;
        editable?: boolean;
    }>(),
    {
        teacherName: '',
        conductedAt: null,
        editable: false,
    },
);

const observations = defineModel<ObservationFields>({ required: true });
const values = computed(() => buildEntranceAnalysis(props.assignments, props.participant?.participant_profile));
const lpsScale = [30, 35, 40, 45, 50, 55, 60, 65, 70];

function columnWidths(widths: number[]) {
    const total = widths.reduce((sum, width) => sum + width, 0);
    return widths.map((width) => `${(width / total) * 100}%`);
}

// Exact table-grid widths extracted from the supplied Word template.
const metaWidths = columnWidths([1889, 3719, 2148, 3164]);
const lpsWidths = columnWidths([2398, 1061, 717, 716, 716, 716, 716, 716, 716, 716, 717, 1015]);
const brtWidths = columnWidths([2398, 1061, 717, 716, 1193, 1193, 1194, 716, 717, 1015]);
const officeWidths = columnWidths([2398, 1061, 1433, 1193, 1193, 1194, 1433, 1015]);
const fpiWidths = columnWidths([2398, 1061, 717, 716, 716, 716, 716, 716, 716, 716, 717, 1015]);
const lmtWidths = columnWidths([4176, 716, 5013, 1015]);
const bitWidths = columnWidths([3459, 941, 941, 924, 924, 924, 938, 930, 1020]);
const concentrationUpperWidths = columnWidths([3498, 1849, 1389, 1389, 1856, 1020]);
const concentrationTotalWidths = columnWidths([10062, 939]);
const concentrationPerformanceWidths = columnWidths([3498, 924, 925, 1389, 1389, 926, 930, 1020]);
const avemWidths = columnWidths([3498, 924, 925, 555, 556, 555, 556, 556, 926, 930, 1020]);
const observationWidths = columnWidths([3498, 7416]);

const fpiDescriptions = [
    ['lebenszufrieden, zuversichtlich', 'unzufrieden, bedrückt'],
    ['hilfsbereit, mitmenschlich', 'selbstbezogen, unsolidarisch'],
    ['ehrgeizig-konkurrierend', 'wenig ehrgeizig-konkurrierend'],
    ['gehemmt, unsicher, kontaktscheu', 'ungezwungen, selbstsicher'],
    ['erregbar, empfindlich, unbeherrscht', 'ruhig, gelassen, selbstbeherrscht'],
    ['spontan, reaktiv, sich durchsetzend', 'wenig aggressiv, kontrolliert'],
    ['angespannt, sich im Stress fühlend', 'wenig beansprucht, belastbar'],
    ['viele Beschwerden, psychosomatisch', 'wenig Beschwerden'],
    ['Furcht vor Erkrankung, sich schonend', 'wenig Gesundheitssorgen'],
    ['Zugeben kleiner Schwächen, ungeniert', 'an Umgangsnormen orientiert'],
    ['extravertiert, gesellig, impulsiv', 'introvertiert, zurückhaltend, ernst'],
    ['emotional, labil, ängstlich, empfindlich', 'emotional stabil, gelassen'],
];

const participantName = computed(() => {
    const surname = String(props.participant?.name ?? '').trim();
    const firstName = String(props.participant?.firstname ?? props.participant?.participant_profile?.firstname ?? '').trim();
    if (!firstName || surname.toLocaleLowerCase('de-DE').includes(firstName.toLocaleLowerCase('de-DE'))) return surname;
    return `${surname}, ${firstName}`;
});

function formatDate(value?: string | null) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
}

function hasMark(value: number | null | undefined, expected: number) {
    return value !== null && value !== undefined && Math.round(Number(value)) === expected;
}

function isInRange(value: number | null | undefined, minimum: number | null, maximum: number | null) {
    if (value === null || value === undefined || Number.isNaN(Number(value))) return false;
    return (minimum === null || value >= minimum) && (maximum === null || value <= maximum);
}

function needsSupport(value: number | null | undefined, threshold: number) {
    return value !== null && value !== undefined && Number(value) < threshold;
}

function concentrationBand(value?: number | null) {
    if (value === null || value === undefined) return null;
    if (value >= 47) return 0;
    if (value >= 34) return 1;
    if (value >= 23) return 2;
    if (value >= 14) return 3;
    if (value >= 6) return 4;
    return 5;
}
</script>

<template>
    <div class="analysis-document">
        <section class="analysis-sheet analysis-page-one">
            <header class="document-header">
                <img src="/images/miqr-logo-grey.jpg" alt="Mitteldeutsches Institut" />
                <div class="document-code">
                    <div>FB_bbU_BPW_E-Com_FOSI_Gruko_IBO</div>
                    <div>_IK_ISO_ISOk_K E-Com_KBM_KGA_KbQ_KbQ-M_KiG_KMQ_KMQ DRV_KQ_</div>
                    <div>MIBO_MISO_MISOk_MWE_OSI_VL_VL bbU_RVL intensiv</div>
                </div>
            </header>

            <h1>Eingangsanalyse/Lehr-, Lern- und Förderbedarf</h1>

            <table class="meta-table">
                <colgroup>
                    <col v-for="(width, index) in metaWidths" :key="index" :style="{ width }" />
                </colgroup>
                <tbody>
                    <tr>
                        <th>Name, Vorname:</th>
                        <td>{{ participantName }}</td>
                        <th>Anleiter:</th>
                        <td>{{ teacherName }}</td>
                    </tr>
                    <tr>
                        <th>Geburtsdatum:</th>
                        <td>{{ formatDate(participant?.participant_profile?.birthday) }}</td>
                        <th>Durchführungsdatum:</th>
                        <td>{{ formatDate(conductedAt) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table scale-table lps-table">
                <colgroup>
                    <col v-for="(width, index) in lpsWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">LPS – Lern-/Leistungsbefähigung</th>
                        <th>- -</th>
                        <th>-</th>
                        <th colspan="2">unterer</th>
                        <th>Ø</th>
                        <th colspan="2">oberer</th>
                        <th>+</th>
                        <th>+ +</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">T-Wert</th>
                        <th v-for="value in lpsScale" :key="value">
                            {{ value === 30 ? '≤ 30' : value === 70 ? '≥ 70' : value }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in values.lps?.rows ?? []" :key="row.key">
                        <td colspan="2">{{ row.label }}</td>
                        <td v-for="value in lpsScale" :key="value" class="mark-cell">{{ hasMark(row.value, value) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ needsSupport(row.value, 40) ? 'X' : '' }}</td>
                    </tr>
                    <template v-if="!values.lps">
                        <tr
                            v-for="label in [
                                '1-2 Allgemeinbildung',
                                '3-4 logisch-schlussfolgerndes Denken',
                                '5-6 Sprachproduktion/-verständnis',
                                '7-10 technische Begabung',
                                '11-12 grafisch-gestalterische Fähigkeit',
                                '13 kurzzeitige Konzentration',
                                '14 Arbeitstempo',
                                '−13 Arbeitssorgfalt',
                            ]"
                            :key="label"
                        >
                            <td colspan="2">{{ label }}</td>
                            <td v-for="number in 10" :key="number"></td>
                        </tr>
                    </template>
                    <tr class="summary-row">
                        <td>
                            <span>GI</span>
                            <em>T-Wert</em>
                        </td>
                        <td class="value-cell">{{ values.lps?.totalT ?? '' }}</td>
                        <td colspan="10" class="blocked-cell"></td>
                    </tr>
                    <tr class="summary-row">
                        <td><em>Prozentrang</em></td>
                        <td class="value-cell">{{ values.lps?.percentile ?? '' }}</td>
                        <td colspan="10" class="blocked-cell"></td>
                    </tr>
                    <tr class="summary-row">
                        <td><em>IQ</em></td>
                        <td class="value-cell">{{ values.lps?.iq ?? '' }}</td>
                        <td colspan="10" class="blocked-cell"></td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table scale-table compact-section brt-table">
                <colgroup>
                    <col v-for="(width, index) in brtWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">Kulturtechnik Rechnen</th>
                        <th>- -</th>
                        <th>-</th>
                        <th>unterer</th>
                        <th>Ø</th>
                        <th>oberer</th>
                        <th>+</th>
                        <th>+ +</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">T-Wert</th>
                        <th>≤ 34</th>
                        <th>35 - 39</th>
                        <th>40 - 45</th>
                        <th>46 - 54</th>
                        <th>55 - 60</th>
                        <th>61 - 66</th>
                        <th>≥ 67</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>BRT</td>
                        <td class="value-cell">{{ values.brtT ?? '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, null, 34) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 35, 39) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 40, 45) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 46, 54) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 55, 60) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 61, 66) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.brtT, 67, null) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ needsSupport(values.brtT, 40) ? 'X' : '' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table scale-table compact-section mrt-table">
                <colgroup>
                    <col v-for="(width, index) in brtWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">Kulturtechnik Schreiben</th>
                        <th>- -</th>
                        <th>-</th>
                        <th>unterer</th>
                        <th>Ø</th>
                        <th>oberer</th>
                        <th>+</th>
                        <th>+ +</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">Prozentrang</th>
                        <th>≤ 5</th>
                        <th>6 - 15</th>
                        <th>16 - 30</th>
                        <th>31 - 68</th>
                        <th>69 - 84</th>
                        <th>85 - 94</th>
                        <th>≥ 95</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MRT</td>
                        <td class="value-cell">{{ values.mrtPercentile ?? '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, null, 5) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 6, 15) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 16, 30) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 31, 68) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 69, 84) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 85, 94) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.mrtPercentile, 95, null) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ needsSupport(values.mrtPercentile, 16) ? 'X' : '' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table scale-table compact-section office-table">
                <colgroup>
                    <col v-for="(width, index) in officeWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">Büropraktische Fähigkeiten</th>
                        <th>-</th>
                        <th>unterer</th>
                        <th>Ø</th>
                        <th>oberer</th>
                        <th>+</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">Rohwert</th>
                        <th>≤ 24</th>
                        <th>25 - 33</th>
                        <th>34 - 40</th>
                        <th>41 - 45</th>
                        <th>≥ 46</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Bürotest</td>
                        <td class="value-cell">{{ values.btRaw ?? '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.btRaw, null, 24) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.btRaw, 25, 33) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.btRaw, 34, 40) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.btRaw, 41, 45) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(values.btRaw, 46, null) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ needsSupport(values.btRaw, 34) ? 'X' : '' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table profile-table">
                <colgroup>
                    <col v-for="(width, index) in fpiWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">FPI – Persönlichkeitsprofil</th>
                        <th colspan="2">+</th>
                        <th colspan="5">Ø</th>
                        <th colspan="2">-</th>
                        <th></th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">Stanine-Wert</th>
                        <th v-for="value in [9, 8, 7, 6, 5, 4, 3, 2, 1]" :key="value">{{ value }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in values.fpi" :key="row.label">
                        <td>{{ index < 10 ? `${index + 1}.` : index === 10 ? 'E.' : 'N.' }} {{ row.label }}</td>
                        <td class="description-cell">{{ fpiDescriptions[index][0] }}</td>
                        <td v-for="value in [9, 8, 7, 6, 5, 4, 3, 2, 1]" :key="value" class="mark-cell">
                            {{ hasMark(row.value, value) ? 'X' : '' }}
                        </td>
                        <td class="description-cell">{{ fpiDescriptions[index][1] }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table lmt-table">
                <colgroup>
                    <col v-for="(width, index) in lmtWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="3">LMT – Leistungs-Motivations-Fragebogen</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th colspan="2">T-Wert</th>
                        <th>Interpretation der Ergebnisse in Worten</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in values.lmt" :key="row.key">
                        <td>
                            <strong>{{ row.key }}</strong> {{ row.label }}
                        </td>
                        <td class="value-cell">{{ row.value ?? '' }}</td>
                        <td>{{ row.interpretation }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <footer class="document-footer">
                <span>Vorlage_Eingangsanalyse_Lehr-,Lern- und Förderbedarf_2026.docx</span>
                <span>Stand 2022-04</span>
            </footer>
        </section>

        <section class="analysis-sheet analysis-page-two">
            <header class="document-header">
                <img src="/images/miqr-logo-grey.jpg" alt="Mitteldeutsches Institut" />
                <div class="document-code">
                    <div>FB_bbU_BPW_E-Com_FOSI_Gruko_IBO</div>
                    <div>_IK_ISO_ISOk_K E-Com_KBM_KGA_KbQ_KbQ-M_KiG_KMQ_KMQ DRV_KQ_</div>
                    <div>MIBO_MISO_MISOk_MWE_OSI_VL_VL bbU_RVL intensiv</div>
                </div>
            </header>

            <table class="analysis-table scale-table bit-table">
                <colgroup>
                    <col v-for="(width, index) in bitWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th>BIT – Berufsinteressen</th>
                        <th colspan="2">-</th>
                        <th colspan="3">Ø</th>
                        <th colspan="2">+</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th>Prozentrang</th>
                        <th>≤ 5</th>
                        <th>≤ 15</th>
                        <th>20 - 30</th>
                        <th>35 - 65</th>
                        <th>70 - 80</th>
                        <th>≥ 85</th>
                        <th>≥ 95</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in values.bit" :key="row.key">
                        <td>
                            <strong>{{ row.key }}</strong
                            >&nbsp;&nbsp; {{ row.label }}
                        </td>
                        <td class="mark-cell">{{ isInRange(row.value, null, 5) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 6, 15) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 20, 30) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 35, 65) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 70, 80) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 85, 94) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ isInRange(row.value, 95, null) ? 'X' : '' }}</td>
                        <td class="mark-cell">{{ needsSupport(row.value, 20) ? 'X' : '' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="concentration-block">
                <table class="analysis-table concentration-table concentration-upper-table">
                    <colgroup>
                        <col v-for="(width, index) in concentrationUpperWidths" :key="index" :style="{ width }" />
                    </colgroup>
                    <thead>
                        <tr class="section-row">
                            <th colspan="6">628</th>
                        </tr>
                        <tr class="performance-group-row">
                            <th></th>
                            <th>Spalte 1</th>
                            <th>Spalte 2</th>
                            <th>Spalte 3</th>
                            <th>Spalte 4</th>
                            <th></th>
                        </tr>
                        <tr class="subheader-row">
                            <th></th>
                            <th colspan="5">Summe der falschen/fehlenden Antworten</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>logisch-schlussfolgerndes Denken</td>
                            <td>{{ values.concentration?.columns[0] ?? '' }} / 10</td>
                            <td>{{ values.concentration?.columns[1] ?? '' }} / 10</td>
                            <td class="blocked-cell"></td>
                            <td class="blocked-cell"></td>
                            <td>{{ values.concentration?.logic ?? '' }} / 20</td>
                        </tr>
                        <tr>
                            <td>Konzentration und Sorgfalt</td>
                            <td class="blocked-cell"></td>
                            <td class="blocked-cell"></td>
                            <td>{{ values.concentration?.columns[2] ?? '' }} / 22</td>
                            <td>{{ values.concentration?.columns[3] ?? '' }} / 24</td>
                            <td>{{ values.concentration?.concentration ?? '' }} / 46</td>
                        </tr>
                    </tbody>
                </table>

                <table class="analysis-table concentration-table concentration-total-table">
                    <colgroup>
                        <col v-for="(width, index) in concentrationTotalWidths" :key="index" :style="{ width }" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td>Gesamtergebnis</td>
                            <td>{{ values.concentration?.total ?? '' }} / 66</td>
                        </tr>
                    </tbody>
                </table>

                <table class="analysis-table concentration-table concentration-performance-table">
                    <colgroup>
                        <col v-for="(width, index) in concentrationPerformanceWidths" :key="index" :style="{ width }" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td class="performance-label">Leistungsbereich</td>
                            <th colspan="2">unterer</th>
                            <th colspan="2">mittlerer</th>
                            <th colspan="2">oberer</th>
                            <td></td>
                        </tr>
                        <tr class="subheader-row">
                            <td class="performance-label">Fehlerpunkte gesamt</td>
                            <td>47 - 66</td>
                            <td>34 - 46</td>
                            <td>23 - 33</td>
                            <td>14 - 22</td>
                            <td>6 - 13</td>
                            <td>0 - 5</td>
                            <td></td>
                        </tr>
                        <tr class="subheader-row">
                            <td></td>
                            <td>≤ 29 %</td>
                            <td>30 - 49 %</td>
                            <td>50 - 66 %</td>
                            <td>67 - 80 %</td>
                            <td>81 - 91 %</td>
                            <td>92 - 100 %</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td v-for="index in 6" :key="index" class="mark-cell">
                                {{ concentrationBand(values.concentration?.total) === index - 1 ? 'X' : '' }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <table class="analysis-table scale-table avem-table">
                <colgroup>
                    <col v-for="(width, index) in avemWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th>AVEM – Arbeitsbezogenes Verhaltens- und Erlebnismuster</th>
                        <th colspan="2">-</th>
                        <th colspan="5">Ø</th>
                        <th colspan="2">+</th>
                        <th rowspan="2">Förder-<br />bedarf</th>
                    </tr>
                    <tr class="subheader-row">
                        <th>Stanine-Wert</th>
                        <th v-for="value in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="value">{{ value }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in values.avem" :key="row.label">
                        <td>{{ row.label }}</td>
                        <td v-for="value in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="value" class="mark-cell">
                            {{ hasMark(row.value, value) ? 'X' : '' }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <table class="analysis-table observations-table">
                <colgroup>
                    <col v-for="(width, index) in observationWidths" :key="index" :style="{ width }" />
                </colgroup>
                <thead>
                    <tr class="section-row">
                        <th colspan="2">Beobachtungen der Anleiter</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Instruktionsverständnis</th>
                        <td>
                            <input v-if="editable" v-model="observations.instruction_understanding" type="text" />
                            <span v-else>{{ observations.instruction_understanding }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Arbeitsweise</th>
                        <td>
                            <input v-if="editable" v-model="observations.work_method" type="text" />
                            <span v-else>{{ observations.work_method }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Arbeitstempo</th>
                        <td>
                            <input v-if="editable" v-model="observations.work_speed" type="text" />
                            <span v-else>{{ observations.work_speed }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Gruppenverhalten</th>
                        <td>
                            <input v-if="editable" v-model="observations.group_behavior" type="text" />
                            <span v-else>{{ observations.group_behavior }}</span>
                        </td>
                    </tr>
                    <tr class="remarks-row">
                        <th>Bemerkungen</th>
                        <td>
                            <textarea v-if="editable" v-model="observations.remarks"></textarea>
                            <span v-else class="remarks-text">{{ observations.remarks }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <footer class="document-footer">
                <span>Vorlage_Eingangsanalyse_Lehr-,Lern- und Förderbedarf_2026.docx</span>
                <span>Stand 2022-04</span>
            </footer>
        </section>
    </div>
</template>

<style scoped>
.analysis-document {
    color: #111;
    font-family: Arial, Helvetica, sans-serif;
}

.analysis-sheet {
    position: relative;
    box-sizing: border-box;
    width: 210mm;
    height: 297mm;
    margin: 0 auto 18px;
    overflow: hidden;
    background: #fff;
    padding: 8mm 10mm 7mm;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.18);
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

.document-header {
    display: flex;
    height: 14mm;
    align-items: flex-start;
    justify-content: space-between;
}

.document-header img {
    width: 67mm;
    height: auto;
    margin-left: 15mm;
}

.document-code {
    max-width: 92mm;
    padding-top: 2mm;
    color: #8a8a8a;
    font-size: 6.5pt;
    line-height: 1.25;
    text-align: right;
}

h1 {
    margin: 5mm 0 5mm;
    font-size: 12pt;
    line-height: 1;
    text-align: center;
}

.meta-table,
.analysis-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.meta-table {
    margin-bottom: 8mm;
    font-size: 9pt;
}

.meta-table th,
.meta-table td {
    height: 7mm;
    border-bottom: 0.45pt solid #111;
    font-weight: 400;
    text-align: left;
    vertical-align: middle;
}

.meta-table th {
    padding-left: 1.5mm;
    white-space: nowrap;
}

.meta-table td {
    padding: 0 2mm;
    font-weight: 600;
}

.analysis-table {
    --dark-border: 1.5pt solid #111;
    --thin-dark-border: 0.5pt solid #111;
    --light-border: 0.5pt solid #bfbfbf;
    --guide-border: 0.5pt dashed #111;
    margin-bottom: 4mm;
    font-size: 7.5pt;
    line-height: 1.08;
}

.analysis-table th,
.analysis-table td {
    box-sizing: border-box;
    height: 5.1mm;
    border: var(--light-border);
    padding: 0.55mm 1.4mm;
    font-weight: 400;
    vertical-align: middle;
}

.analysis-table td:first-child,
.analysis-table th:first-child {
    border-left: var(--thin-dark-border);
}

.analysis-table td:last-child,
.analysis-table th:last-child {
    border-right: var(--thin-dark-border);
}

.analysis-table tr:last-child td,
.analysis-table tr:last-child th {
    border-bottom: var(--thin-dark-border);
}

.section-row th {
    height: 6.2mm;
    border: 0 solid transparent;
    border-top: var(--thin-dark-border);
    border-bottom: var(--light-border);
    background: #bdbdbd;
    font-size: 7.6pt;
    font-weight: 700;
    text-align: center;
}

.section-row th:first-child {
    border-left: var(--thin-dark-border);
    text-align: left;
}

.section-row th:last-child {
    border-right: var(--thin-dark-border);
}

.lps-table .section-row > :nth-child(1),
.lps-table .section-row > :nth-child(3),
.lps-table .section-row > :nth-child(6),
.lps-table .section-row > :nth-child(8),
.brt-table .section-row > :nth-child(1),
.brt-table .section-row > :nth-child(3),
.brt-table .section-row > :nth-child(6),
.brt-table .section-row > :nth-child(8),
.mrt-table .section-row > :nth-child(1),
.mrt-table .section-row > :nth-child(3),
.mrt-table .section-row > :nth-child(6),
.mrt-table .section-row > :nth-child(8) {
    border-right: var(--dark-border);
}

.lps-table .section-row > :nth-child(2),
.lps-table .section-row > :nth-child(7),
.brt-table .section-row > :nth-child(2),
.brt-table .section-row > :nth-child(7),
.mrt-table .section-row > :nth-child(2),
.mrt-table .section-row > :nth-child(7) {
    border-right: var(--thin-dark-border);
}

.lps-table .section-row > :last-child,
.brt-table .section-row > :last-child,
.mrt-table .section-row > :last-child,
.office-table .section-row > :last-child,
.profile-table .section-row > :last-child,
.bit-table .section-row > :last-child,
.avem-table .section-row > :last-child {
    border-left: var(--dark-border);
}

.office-table .section-row > :nth-child(1),
.office-table .section-row > :nth-child(2),
.office-table .section-row > :nth-child(5),
.office-table .section-row > :nth-child(6) {
    border-right: var(--dark-border);
}

.profile-table .section-row > :nth-child(1),
.profile-table .section-row > :nth-child(2),
.profile-table .section-row > :nth-child(3),
.profile-table .section-row > :nth-child(4),
.bit-table .section-row > :nth-child(1),
.bit-table .section-row > :nth-child(2),
.bit-table .section-row > :nth-child(3),
.bit-table .section-row > :nth-child(4),
.avem-table .section-row > :nth-child(1),
.avem-table .section-row > :nth-child(2),
.avem-table .section-row > :nth-child(3),
.avem-table .section-row > :nth-child(4) {
    border-right: var(--dark-border);
}

.lmt-table .section-row > :first-child {
    border-right: var(--dark-border);
}

.lmt-table .section-row > :last-child {
    border-left: var(--dark-border);
}

.lps-table .subheader-row > :nth-child(1),
.lps-table .subheader-row > :nth-child(3),
.lps-table .subheader-row > :nth-child(8),
.lps-table .subheader-row > :nth-child(10),
.lps-table tbody tr:not(.summary-row) > :nth-child(1),
.lps-table tbody tr:not(.summary-row) > :nth-child(3),
.lps-table tbody tr:not(.summary-row) > :nth-child(8),
.lps-table tbody tr:not(.summary-row) > :nth-child(10),
.lps-table .summary-row > :nth-child(2) {
    border-right: var(--dark-border);
}

.lps-table .subheader-row > :nth-child(2),
.lps-table .subheader-row > :nth-child(9),
.lps-table tbody tr:not(.summary-row) > :nth-child(2),
.lps-table tbody tr:not(.summary-row) > :nth-child(9) {
    border-right: var(--thin-dark-border);
}

.lps-table tbody tr:not(.summary-row) > :last-child {
    border-left: var(--dark-border);
}

.brt-table .subheader-row > :nth-child(1),
.brt-table .subheader-row > :nth-child(3),
.brt-table .subheader-row > :nth-child(6),
.brt-table .subheader-row > :nth-child(8),
.mrt-table .subheader-row > :nth-child(1),
.mrt-table .subheader-row > :nth-child(3),
.mrt-table .subheader-row > :nth-child(6),
.mrt-table .subheader-row > :nth-child(8),
.brt-table tbody > tr > :nth-child(2),
.brt-table tbody > tr > :nth-child(4),
.brt-table tbody > tr > :nth-child(7),
.brt-table tbody > tr > :nth-child(9),
.mrt-table tbody > tr > :nth-child(2),
.mrt-table tbody > tr > :nth-child(4),
.mrt-table tbody > tr > :nth-child(7),
.mrt-table tbody > tr > :nth-child(9) {
    border-right: var(--dark-border);
}

.brt-table .subheader-row > :nth-child(2),
.brt-table .subheader-row > :nth-child(7),
.mrt-table .subheader-row > :nth-child(2),
.mrt-table .subheader-row > :nth-child(7),
.brt-table tbody > tr > :nth-child(3),
.brt-table tbody > tr > :nth-child(8),
.mrt-table tbody > tr > :nth-child(3),
.mrt-table tbody > tr > :nth-child(8) {
    border-right: var(--thin-dark-border);
}

.brt-table tbody > tr > :last-child,
.mrt-table tbody > tr > :last-child,
.office-table tbody > tr > :last-child {
    border-left: var(--dark-border);
}

.office-table .subheader-row > :nth-child(1),
.office-table .subheader-row > :nth-child(2),
.office-table .subheader-row > :nth-child(5),
.office-table .subheader-row > :nth-child(6),
.office-table tbody > tr > :nth-child(2),
.office-table tbody > tr > :nth-child(3),
.office-table tbody > tr > :nth-child(6),
.office-table tbody > tr > :nth-child(7) {
    border-right: var(--dark-border);
}

.profile-table .subheader-row > :nth-child(1),
.profile-table .subheader-row > :nth-child(3),
.profile-table .subheader-row > :nth-child(8),
.profile-table .subheader-row > :nth-child(10),
.profile-table tbody > tr > :nth-child(2),
.profile-table tbody > tr > :nth-child(4),
.profile-table tbody > tr > :nth-child(9),
.profile-table tbody > tr > :nth-child(11) {
    border-right: var(--dark-border);
}

.profile-table .subheader-row > :nth-child(4),
.profile-table .subheader-row > :nth-child(7),
.profile-table tbody > tr > :nth-child(5),
.profile-table tbody > tr > :nth-child(8) {
    border-right: var(--guide-border);
}

.lmt-table .subheader-row > :nth-child(1),
.lmt-table .subheader-row > :nth-child(2),
.lmt-table tbody > tr > :nth-child(2),
.lmt-table tbody > tr > :nth-child(3) {
    border-right: var(--dark-border);
}

.bit-table .subheader-row > :nth-child(1),
.bit-table .subheader-row > :nth-child(3),
.bit-table .subheader-row > :nth-child(6),
.bit-table .subheader-row > :nth-child(8),
.bit-table tbody > tr > :nth-child(1),
.bit-table tbody > tr > :nth-child(3),
.bit-table tbody > tr > :nth-child(6),
.bit-table tbody > tr > :nth-child(8) {
    border-right: var(--dark-border);
}

.bit-table tbody > tr > :last-child {
    border-left: var(--dark-border);
}

.subheader-row th {
    height: 4.4mm;
    color: #a1a1a1;
    font-size: 6.8pt;
    font-style: italic;
    text-align: center;
}

.subheader-row th:first-child {
    text-align: right;
}

.mark-cell,
.value-cell {
    font-size: 8.5pt;
    font-weight: 700 !important;
    text-align: center;
}

.summary-row td {
    height: 5.1mm;
}

.summary-row td:first-child {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.summary-row em {
    color: #a1a1a1;
    font-size: 6.8pt;
    font-weight: 400;
    margin-left: auto;
    text-align: right;
}

.compact-section {
    margin-bottom: 0;
}

.profile-table {
    margin-top: 4mm;
    margin-bottom: 4mm;
    font-size: 6.8pt;
}

.profile-table th,
.profile-table td {
    height: 4.9mm;
    padding: 0.25mm 1mm;
}

.description-cell {
    color: #888;
    font-size: 4.7pt;
    line-height: 1.05;
    text-align: center;
}

.lmt-table {
    margin-bottom: 0;
}

.lmt-table th,
.lmt-table td {
    height: 5.1mm;
}

.analysis-page-two {
    padding-top: 8mm;
}

.analysis-page-two .document-header {
    margin-bottom: 5mm;
}

.bit-table {
    margin-bottom: 4mm;
}

.concentration-block {
    margin-bottom: 4mm;
}

.concentration-block .concentration-table {
    margin-bottom: 0;
}

.concentration-table th,
.concentration-table td {
    height: 5mm;
    text-align: center;
}

.concentration-upper-table th:first-child,
.concentration-upper-table td:first-child,
.concentration-performance-table th:first-child,
.concentration-performance-table td:first-child {
    text-align: left;
}

.concentration-upper-table tr:not(.section-row) > :nth-child(1),
.concentration-upper-table tr:not(.section-row) > :nth-child(5),
.concentration-total-table tr > :nth-child(1),
.concentration-performance-table tr:not(.performance-group-row) > :nth-child(1),
.concentration-performance-table tr:not(.performance-group-row) > :nth-child(3),
.concentration-performance-table tr:not(.performance-group-row) > :nth-child(5),
.concentration-performance-table tr:not(.performance-group-row) > :nth-child(7),
.concentration-performance-table .performance-group-row > :nth-child(1),
.concentration-performance-table .performance-group-row > :nth-child(2),
.concentration-performance-table .performance-group-row > :nth-child(3) {
    border-right: var(--dark-border);
}

.concentration-performance-table .performance-group-row > :last-child {
    border-left: var(--dark-border);
}

.concentration-upper-table tr:not(.section-row) > :nth-child(2),
.concentration-upper-table tr:not(.section-row) > :nth-child(3),
.concentration-upper-table tr:not(.section-row) > :nth-child(4) {
    border-right: var(--thin-dark-border);
}

.concentration-total-table td:first-child {
    color: #a1a1a1;
    font-size: 6.8pt;
    font-style: italic;
    text-align: right;
}

.concentration-total-table td:last-child {
    color: #777;
    text-align: center;
}

.blocked-cell {
    background: #000;
}

.performance-label {
    text-align: right !important;
}

.avem-table {
    margin-bottom: 5mm;
}

.avem-table th,
.avem-table td {
    height: 5mm;
}

.avem-table .subheader-row > :nth-child(1),
.avem-table .subheader-row > :nth-child(3),
.avem-table .subheader-row > :nth-child(8),
.avem-table .subheader-row > :nth-child(10),
.avem-table tbody > tr > :nth-child(1),
.avem-table tbody > tr > :nth-child(3),
.avem-table tbody > tr > :nth-child(8),
.avem-table tbody > tr > :nth-child(10) {
    border-right: var(--dark-border);
}

.avem-table .subheader-row > :nth-child(4),
.avem-table .subheader-row > :nth-child(7),
.avem-table tbody > tr > :nth-child(4),
.avem-table tbody > tr > :nth-child(7) {
    border-right: var(--guide-border);
}

.avem-table tbody > tr > :last-child {
    border-left: var(--dark-border);
}

.observations-table {
    margin-bottom: 0;
    font-size: 8pt;
}

.observations-table th,
.observations-table td {
    height: 6mm;
}

.observations-table tbody th {
    font-weight: 400;
    text-align: left;
    border-right: var(--dark-border);
}

.observations-table input,
.observations-table textarea {
    width: 100%;
    height: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    padding: 0.5mm 1mm;
    font: inherit;
    resize: none;
}

.observations-table input:focus,
.observations-table textarea:focus {
    background: #eff6ff;
    box-shadow: inset 0 0 0 1px #3b82f6;
}

.observations-table .remarks-row th,
.observations-table .remarks-row td {
    height: 24mm;
}

.remarks-text {
    display: block;
    white-space: pre-wrap;
}

.document-footer {
    position: absolute;
    right: 10mm;
    bottom: 3.2mm;
    left: 12mm;
    display: flex;
    justify-content: space-between;
    color: #999;
    font-size: 3.2pt;
}

@media print {
    .analysis-sheet {
        margin: 0;
        box-shadow: none;
        break-after: page;
        page-break-after: always;
    }

    .analysis-sheet:last-child {
        break-after: auto;
        page-break-after: auto;
    }
}
</style>

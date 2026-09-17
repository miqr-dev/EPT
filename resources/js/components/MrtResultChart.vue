<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        groupScores?: Array<number | string | null | undefined> | null;
        groupStanines?: Array<number | string | null | undefined> | null;
        totalScore?: number | string | null;
        prozentrang?: number | string | null;
        pdfMode?: boolean;
    }>(),
    {
        groupScores: null,
        groupStanines: null,
        totalScore: null,
        prozentrang: null,
        pdfMode: false,
    },
);

const groupLabels = ['U1', 'U2', 'U3', 'U4', 'U5', 'U6'];
const percentTicks = ['4%', '11%', '23%', '40%', '60%', '77%', '89%', '96%', '100%'];
const percentBreaks = [4, 11, 23, 40, 60, 77, 89, 96, 100];

const chart = {
    width: 780,
    height: 516,
    plotLeft: 96,
    plotTop: 74,
    plotWidth: 640,
    rowStep: 56,
    bottomRowGap: 34,
    bottomAxisHeight: 28,
    scoreBoxX: 56,
    valueBoxSize: 26,
};

const plotBottom = chart.plotTop + chart.rowStep * groupLabels.length;
const bottomAxisTop = plotBottom + chart.bottomRowGap;
const bottomAxisBottom = bottomAxisTop + chart.bottomAxisHeight;
const plotCenterX = chart.plotLeft + chart.plotWidth / 2;
const snLabelY = chart.plotTop - 30;
const titleY = chart.plotTop - 54;
const rwLabelY = snLabelY;
const sideLabelX = chart.scoreBoxX - 8;
const percentLabelY = bottomAxisBottom + 19;
const prBoxY = bottomAxisBottom - chart.valueBoxSize;
const totalScoreY = plotBottom - chart.valueBoxSize / 2;

const snTicks = Array.from({ length: 9 }, (_, index) => {
    const value = index + 1;

    return {
        value,
        x: xForScaleValue(value),
    };
});

const internalTicks = snTicks.slice(1, -1);
const horizontalGridLines = Array.from({ length: groupLabels.length - 1 }, (_, index) => chart.plotTop + chart.rowStep * (index + 1));

const snNormalBand = computed(() => {
    const start = xForScaleValue(4);
    const end = xForScaleValue(6);

    return {
        x: start,
        width: end - start,
    };
});

const percentNormalBand = computed(() => {
    const start = xForScaleValue(prToX(16));
    const end = xForScaleValue(prToX(84));

    return {
        x: start,
        width: end - start,
    };
});

const rows = computed(() =>
    groupLabels.map((label, index) => ({
        label,
        score: formatBoxValue(props.groupScores?.[index]),
        y: chart.plotTop + chart.rowStep * index,
    })),
);

const staninePoints = computed(() =>
    groupLabels
        .map((_, index) => {
            const value = toNumber(props.groupStanines?.[index]);

            if (value == null) {
                return null;
            }

            return {
                x: xForScaleValue(value),
                y: chart.plotTop + chart.rowStep * index,
            };
        })
        .filter((point): point is { x: number; y: number } => point !== null),
);

const staninePath = computed(() =>
    staninePoints.value.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`).join(' '),
);

const prozentrangPoint = computed(() => {
    const value = toNumber(props.prozentrang);

    if (value == null) {
        return null;
    }

    return {
        x: xForScaleValue(prToX(value)),
        y: bottomAxisTop + chart.bottomAxisHeight / 2,
    };
});

const totalScoreLabel = computed(() => {
    const scores = props.groupScores?.map((score) => toNumber(score)) ?? [];

    if (scores.length >= groupLabels.length && scores.every((score) => score != null)) {
        return formatBoxValue(scores.reduce((sum, score) => sum + (score ?? 0), 0));
    }

    return formatBoxValue(props.totalScore);
});

const prozentrangLabel = computed(() => formatBoxValue(props.prozentrang));

function toNumber(value: number | string | null | undefined): number | null {
    if (value == null || value === '') {
        return null;
    }

    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
}

function formatBoxValue(value: number | string | null | undefined): string {
    const parsed = toNumber(value);

    if (parsed == null) {
        return '-';
    }

    return Number.isInteger(parsed) ? String(parsed) : parsed.toFixed(1);
}

function prToX(pr: number): number {
    const p = Math.max(0, Math.min(100, pr));

    if (p <= percentBreaks[0]) {
        return 1;
    }

    if (p >= percentBreaks[percentBreaks.length - 1]) {
        return 9;
    }

    for (let index = 0; index < percentBreaks.length - 1; index += 1) {
        const start = percentBreaks[index];
        const end = percentBreaks[index + 1];

        if (p >= start && p <= end) {
            return index + 1 + (p - start) / (end - start);
        }
    }

    return 1;
}

function xForScaleValue(value: number): number {
    const clamped = Math.max(1, Math.min(9, value));

    return chart.plotLeft + ((clamped - 1) / 8) * chart.plotWidth;
}
</script>

<template>
    <svg
        class="mrt-result-chart"
        :class="{ 'mrt-result-chart--pdf': pdfMode }"
        :viewBox="`0 0 ${chart.width} ${chart.height}`"
        role="img"
        aria-label="MRT Ergebnisdiagramm"
    >
        <text class="mrt-result-chart__title" :x="plotCenterX" :y="titleY" text-anchor="middle">SN - Werte</text>

        <rect
            class="mrt-result-chart__plot-background"
            :x="chart.plotLeft"
            :y="chart.plotTop"
            :width="chart.plotWidth"
            :height="plotBottom - chart.plotTop"
        />
        <rect
            class="mrt-result-chart__normal-band"
            :x="snNormalBand.x"
            :y="chart.plotTop"
            :width="snNormalBand.width"
            :height="plotBottom - chart.plotTop"
        />

        <g class="mrt-result-chart__grid">
            <line v-for="tick in internalTicks" :key="`vertical-${tick.value}`" :x1="tick.x" :x2="tick.x" :y1="chart.plotTop" :y2="plotBottom" />
            <line
                v-for="(y, index) in horizontalGridLines"
                :key="`horizontal-${index}`"
                :x1="chart.plotLeft"
                :x2="chart.plotLeft + chart.plotWidth"
                :y1="y"
                :y2="y"
            />
        </g>

        <text v-for="tick in snTicks" :key="`sn-${tick.value}`" class="mrt-result-chart__top-tick" :x="tick.x" :y="snLabelY" text-anchor="middle">
            {{ tick.value }}
        </text>

        <line
            class="mrt-result-chart__frame-side"
            :x1="chart.plotLeft"
            :x2="chart.plotLeft + chart.plotWidth"
            :y1="chart.plotTop"
            :y2="chart.plotTop"
        />
        <line class="mrt-result-chart__frame-side" :x1="chart.plotLeft" :x2="chart.plotLeft" :y1="chart.plotTop" :y2="plotBottom" />
        <line
            class="mrt-result-chart__frame-side"
            :x1="chart.plotLeft + chart.plotWidth"
            :x2="chart.plotLeft + chart.plotWidth"
            :y1="chart.plotTop"
            :y2="plotBottom"
        />
        <line class="mrt-result-chart__frame-side" :x1="chart.plotLeft" :x2="chart.plotLeft + chart.plotWidth" :y1="plotBottom" :y2="plotBottom" />
        <rect
            class="mrt-result-chart__bottom-axis-row"
            :x="chart.plotLeft"
            :y="bottomAxisTop"
            :width="chart.plotWidth"
            :height="chart.bottomAxisHeight"
        />
        <rect
            class="mrt-result-chart__normal-band"
            :x="percentNormalBand.x"
            :y="bottomAxisTop"
            :width="percentNormalBand.width"
            :height="chart.bottomAxisHeight"
        />
        <line
            v-for="tick in internalTicks"
            :key="`bottom-tick-${tick.value}`"
            class="mrt-result-chart__bottom-axis-tick"
            :x1="tick.x"
            :x2="tick.x"
            :y1="bottomAxisTop"
            :y2="bottomAxisBottom"
        />
        <rect
            class="mrt-result-chart__bottom-axis-frame"
            :x="chart.plotLeft"
            :y="bottomAxisTop"
            :width="chart.plotWidth"
            :height="chart.bottomAxisHeight"
        />

        <g class="mrt-result-chart__rw-labels">
            <text class="mrt-result-chart__axis-label" :x="chart.scoreBoxX + chart.valueBoxSize / 2" :y="rwLabelY" text-anchor="middle">RW</text>
            <g v-for="row in rows" :key="row.label">
                <text class="mrt-result-chart__group-label" :x="sideLabelX" :y="row.y + 5" text-anchor="end">
                    {{ row.label }}
                </text>
                <rect
                    class="mrt-result-chart__value-box"
                    :x="chart.scoreBoxX"
                    :y="row.y - chart.valueBoxSize / 2"
                    :width="chart.valueBoxSize"
                    :height="chart.valueBoxSize"
                />
                <text class="mrt-result-chart__value-text" :x="chart.scoreBoxX + chart.valueBoxSize / 2" :y="row.y + 4" text-anchor="middle">
                    {{ row.score }}
                </text>
            </g>
        </g>

        <path v-if="staninePath" class="mrt-result-chart__line" :d="staninePath" />
        <circle v-for="(point, index) in staninePoints" :key="`point-${index}`" class="mrt-result-chart__point" :cx="point.x" :cy="point.y" r="8" />

        <rect
            class="mrt-result-chart__value-box"
            :x="chart.scoreBoxX"
            :y="totalScoreY - chart.valueBoxSize / 2"
            :width="chart.valueBoxSize"
            :height="chart.valueBoxSize"
        />
        <text class="mrt-result-chart__value-text" :x="chart.scoreBoxX + chart.valueBoxSize / 2" :y="totalScoreY + 4" text-anchor="middle">
            {{ totalScoreLabel }}
        </text>

        <g class="mrt-result-chart__pr-labels">
            <rect class="mrt-result-chart__value-box" :x="chart.scoreBoxX" :y="prBoxY" :width="chart.valueBoxSize" :height="chart.valueBoxSize" />
            <text
                class="mrt-result-chart__value-text"
                :x="chart.scoreBoxX + chart.valueBoxSize / 2"
                :y="prBoxY + chart.valueBoxSize / 2 + 4"
                text-anchor="middle"
            >
                {{ prozentrangLabel }}
            </text>
            <text class="mrt-result-chart__axis-label" :x="sideLabelX" :y="prBoxY + chart.valueBoxSize / 2 + 5" text-anchor="end">PR</text>
            <text
                v-for="(label, index) in percentTicks"
                :key="label"
                class="mrt-result-chart__percent-tick"
                :x="snTicks[index].x"
                :y="percentLabelY"
                text-anchor="middle"
            >
                {{ label }}
            </text>
        </g>

        <circle v-if="prozentrangPoint" class="mrt-result-chart__pr-point" :cx="prozentrangPoint.x" :cy="prozentrangPoint.y" r="7" />
    </svg>
</template>

<style scoped>
.mrt-result-chart {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
    color: #111827;
    font-family: Arial, Helvetica, sans-serif;
}

.mrt-result-chart__title {
    fill: currentColor;
    font-size: 15px;
    font-weight: 700;
}

.mrt-result-chart__plot-background {
    fill: #ffffff;
}

.mrt-result-chart__normal-band {
    fill: rgba(34, 197, 94, 0.18);
}

.mrt-result-chart__grid line {
    stroke: #cfd4dc;
    stroke-width: 1;
}

.mrt-result-chart__bottom-axis-row {
    fill: #ffffff;
}

.mrt-result-chart__bottom-axis-frame {
    fill: none;
    stroke: #000000;
    stroke-width: 1.25;
}

.mrt-result-chart__bottom-axis-tick {
    stroke: #cfd4dc;
    stroke-width: 1;
}

.mrt-result-chart__frame-side {
    stroke: #111827;
    stroke-width: 1.25;
}

.mrt-result-chart__top-tick,
.mrt-result-chart__percent-tick {
    fill: #6b7280;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
}

.mrt-result-chart__axis-label {
    fill: currentColor;
    font-size: 14px;
    font-weight: 700;
}

.mrt-result-chart__value-box {
    fill: #ffffff;
    stroke: #111827;
    stroke-width: 1.25;
}

.mrt-result-chart__value-text {
    fill: #111827;
    font-size: 13px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.mrt-result-chart__group-label {
    fill: #111827;
    font-size: 13px;
    font-weight: 700;
}

.mrt-result-chart__line {
    fill: none;
    stroke: #2563eb;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 3;
}

.mrt-result-chart__point {
    fill: #2563eb;
}

.mrt-result-chart__pr-point {
    fill: #dc2626;
    stroke: #dc2626;
    stroke-width: 2;
}

.mrt-result-chart--pdf {
    max-width: 100%;
}
</style>

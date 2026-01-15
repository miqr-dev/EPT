/**
 * Altersgruppen-Schlüssel für die LPS‑B Auswertung.    
 * Diese Bezeichnungen sagen nur, in welche Altersklasse eine Person fällt.
 */
export type LpsBAgeGroupKey = 'under_18' | '19_20' | '21_29' | '30_39' | '40_49' | '50_plus';

/**
 * Eine Zeile in einer Einzeltabelle:
 * - raw = Rohwert aus dem Test
 * - t = standardisierter T‑Wert
 * - c = standardisierter C‑Wert
 */
export type LpsBScoreRow = {
  raw: number;
  t: number;
  c: number;
};

/**
 * Bereichszeile für Gesamtwerte:
 * - min/max = Rohwertbereich, der dieselben Normwerte erhält
 * - t/c = standardisierte Werte
 */
export type LpsBScoreRangeRow = {
  min: number;
  max: number;
  t: number;
  c: number;
};

/**
 * Gesamtwertzeile mit optionalem Prozentrang (pr).
 * pr zeigt, wie viel Prozent der Vergleichsgruppe niedriger liegen.
 */
export type LpsBTotalScoreRow = (LpsBScoreRow | LpsBScoreRangeRow) & { pr?: number };

/**
 * Schlüssel für die einzelnen Untertests bzw. zusammengesetzten Tests.
 * Die Bezeichnungen kommen direkt aus dem LPS‑B Testschema.
 */
export type LpsBScoreKey =
  | 'test_1_2'
  | 'test_3'
  | 'test_4'
  | 'test_3_4'
  | 'test_5'
  | 'test_6'
  | 'test_5_6'
  | 'test_7'
  | 'test_8'
  | 'test_9'
  | 'test_10'
  | 'test_7_10'
  | 'test_11'
  | 'test_12'
  | 'test_11_12'
  | 'test_13'
  | 'test_14'
  | 'test_13_14'
  | 'test_14_wrong';

/**
 * Vollständige Tabelle für alle Altersgruppen.
 * Jede Altersgruppe hat:
 * - columns: die Einzel- und Untertesttabellen
 * - total: die Gesamtwerttabelle
 */
export type LpsBScoreTables = Record<
  LpsBAgeGroupKey,
  {
    columns: Partial<Record<LpsBScoreKey, Array<LpsBScoreRow | LpsBScoreRangeRow>>>;
    total: LpsBTotalScoreRow[];
  }
>;

/** Fixe T‑Werte (Normwerte) für die Skalen. */
const LPS_B_T_VALUES = [30, 35, 40, 45, 50, 55, 60, 65, 70] as const;
/** Fixe C‑Werte (Normwerte) für die Skalen. */
const LPS_B_C_VALUES = [1, 2, 3, 4, 5, 6, 7, 8, 9] as const;

/**
 * C‑Werte für die Gesamtwertung (feiner abgestuft als bei Einzeltests).
 */
const LPS_B_TOTAL_C_VALUES = [
  0.0, 1.0, 1.2, 1.4, 1.6, 1.8, 2.0, 2.2, 2.4, 2.6, 2.8, 3.0, 3.2, 3.4, 3.6, 3.8, 4.0, 4.2, 4.4, 4.6,
  4.8, 5.0, 5.2, 5.4, 5.6, 5.8, 6.0, 6.2, 6.4, 6.6, 6.8, 7.0, 7.2, 7.4, 7.6, 7.8, 8.0, 8.2, 8.4, 8.6,
  8.8, 9.0, 9.2, 9.4, 9.6, 9.8, 10.0, 10.2, 10.4, 10.6, 10.8, 11.0,
] as const;

/**
 * T‑Werte für die Gesamtwertung.
 */
const LPS_B_TOTAL_T_VALUES = [
  25, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55,
  56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80,
] as const;

/**
 * Prozentränge (pr) zur Gesamtwertung.
 * Ein höherer Wert bedeutet eine bessere Platzierung in der Vergleichsgruppe.
 */
const LPS_B_TOTAL_PR_VALUES = [
  0.6, 2.3, 2.9, 3.6, 4.5, 5.5, 6.7, 8.1, 9.7, 11.5, 13.6, 15.9, 18.4, 21.2, 24.2, 27.4, 30.8, 34.5, 38.2,
  42.1, 46.0, 50.0, 54.0, 57.9, 61.8, 65.5, 69.2, 72.6, 75.8, 78.8, 81.6, 84.1, 86.4, 88.5, 90.3, 91.9,
  93.3, 94.5, 95.5, 96.4, 97.1, 97.7, 98.2, 98.6, 98.9, 99.2, 99.4, 99.5, 99.6, 99.7, 99.8, 99.9,
] as const;

/**
 * Baut die Tabellenzeilen für einzelne Tests auf.
 * Eingabe: Liste der Rohwerte (null = kein Eintrag).
 * Ausgabe: Liste aus Rohwert + zugehörigem T‑ und C‑Wert.
 */
function buildScoreRows(values: Array<number | null>): LpsBScoreRow[] {
  return values.flatMap((raw, index) => {
    if (raw === null || raw === undefined) return [];
    return [
      {
        raw,
        t: LPS_B_T_VALUES[index] ?? 0,
        c: LPS_B_C_VALUES[index] ?? 0,
      },
    ];
  });
}

/**
 * Baut die Gesamtwert-Tabelle.
 * Jeder Rohwert wird zu einem Bereich (min/max) zusammengefasst
 * und mit T‑, C‑Wert sowie Prozentrang verknüpft.
 */
function buildTotalRows(rawValues: number[]): LpsBTotalScoreRow[] {
  return rawValues.map((raw, index) => {
    const nextRaw = rawValues[index + 1];
    const max = typeof nextRaw === 'number' ? nextRaw - 1 : raw;
    return {
      min: raw,
      max,
      t: LPS_B_TOTAL_T_VALUES[index] ?? 0,
      c: LPS_B_TOTAL_C_VALUES[index] ?? 0,
      pr: LPS_B_TOTAL_PR_VALUES[index] ?? 0,
    };
  });
}

/**
 * Rohwertgrenzen für die Gesamtwertung je Altersgruppe.
 * Diese Werte werden später in verständliche Bereiche umgerechnet.
 */
const LPS_B_TOTAL_UNDER_18 = [
  77, 119, 127, 135, 143, 151, 158, 166, 174, 182, 189, 196, 203, 210, 217, 224, 230, 239, 248, 256, 264, 272, 282,
  292, 302, 313, 322, 331, 340, 349, 357, 365, 373, 380, 387, 394, 401, 407, 412, 417, 422, 427, 433, 438, 443, 448,
  453, 459, 464, 469, 474, 479,
];

/** Rohwertgrenzen für 19–20 Jahre. */
const LPS_B_TOTAL_19_20 = [
  64, 102, 111, 119, 127, 135, 143, 152, 161, 170, 179, 187, 195, 203, 211, 219, 226, 236, 245, 254, 263, 272, 283,
  294, 305, 316, 326, 336, 345, 354, 363, 372, 380, 388, 396, 404, 412, 418, 423, 428, 433, 438, 443, 448, 453, 458,
  463, 469, 474, 479, 484, 489,
];

/** Rohwertgrenzen für 21–29 Jahre. */
const LPS_B_TOTAL_21_29 = [
  51, 86, 94, 102, 110, 118, 126, 135, 144, 153, 162, 171, 181, 190, 199, 208, 217, 227, 237, 247, 257, 267, 278, 289,
  300, 311, 322, 332, 342, 352, 362, 371, 380, 389, 398, 407, 415, 421, 427, 432, 437, 442, 447, 452, 457, 462, 467,
  472, 477, 482, 487, 492,
];

/** Rohwertgrenzen für 30–39 Jahre. */
const LPS_B_TOTAL_30_39 = [
  36, 69, 76, 83, 90, 97, 104, 114, 124, 134, 143, 152, 162, 172, 182, 192, 201, 212, 223, 234, 245, 256, 267, 278,
  289, 299, 309, 320, 330, 340, 350, 360, 370, 379, 388, 397, 406, 411, 417, 423, 429, 437, 442, 447, 452, 457, 462,
  467, 472, 477, 482, 487,
];

/** Rohwertgrenzen für 40–49 Jahre. */
const LPS_B_TOTAL_40_49 = [
  21, 51, 58, 65, 72, 79, 86, 96, 106, 115, 124, 133, 143, 153, 163, 173, 182, 193, 204, 215, 226, 236, 247, 258, 269,
  279, 289, 300, 311, 322, 332, 341, 350, 359, 368, 376, 385, 392, 399, 406, 412, 418, 424, 429, 434, 439, 444, 449,
  454, 459, 464, 469,
];

/** Rohwertgrenzen für 50+ Jahre. */
const LPS_B_TOTAL_50_PLUS = [
  3, 29, 36, 43, 50, 57, 63, 71, 79, 87, 95, 103, 113, 123, 132, 141, 150, 160, 170, 180, 190, 199, 209, 219, 229,
  239, 249, 260, 270, 280, 290, 300, 309, 318, 327, 336, 345, 352, 359, 366, 373, 379, 386, 393, 399, 405, 411, 416,
  421, 426, 431, 436,
];

/** Anzeigenamen der Altersgruppen für die Oberfläche. */
export const LPS_B_AGE_GROUP_LABELS: Record<LpsBAgeGroupKey, string> = {
  under_18: 'Bis 18',
  '19_20': '19–20',
  '21_29': '21–29',
  '30_39': '30–39',
  '40_49': '40–49',
  '50_plus': '50+',
};

/**
 * Zentrale Tabelle mit allen Normwerten.
 * Hier sind pro Altersgruppe die Rohwerte der Untertests sowie die Gesamtwerte hinterlegt.
 */
export const LPS_B_SCORE_TABLES: LpsBScoreTables = {
  under_18: {
    columns: {
      test_1_2: buildScoreRows([6, 11, 18, 25, 33, 42, 51, 59, 66]),
      test_3: buildScoreRows([9, 12, 15, 19, 22, 26, 29, 31, 34]),
      test_4: buildScoreRows([11, 14, 18, 21, 24, 26, 28, 30, 33]),
      test_3_4: buildScoreRows([23, 30, 35, 41, 47, 52, 56, 61, 65]),
      test_5: buildScoreRows([1, 3, 6, 10, 17, 24, 29, 34, 38]),
      test_6: buildScoreRows([5, 10, 16, 21, 26, 31, 37, 43, 49]),
      test_5_6: buildScoreRows([7, 17, 25, 34, 43, 55, 65, 73, 81]),
      test_7: buildScoreRows([5, 7, 9, 12, 16, 19, 23, 27, 32]),
      test_8: buildScoreRows([null, 1, 5, 9, 14, 23, 33, 39, 40]),
      test_9: buildScoreRows([4, 9, 14, 18, 23, 26, 30, 32, 35]),
      test_10: buildScoreRows([3, 7, 11, 14, 18, 23, 27, 32, 37]),
      test_7_10: buildScoreRows([23, 33, 45, 57, 70, 85, 101, 118, 132]),
      test_11: buildScoreRows([7, 12, 16, 19, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([5, 10, 15, 19, 23, 27, 30, 33, 36]),
      test_11_12: buildScoreRows([14, 23, 33, 40, 46, 52, 58, 63, 69]),
      test_13: buildScoreRows([2, 4, 7, 9, 12, 15, 17, 21, 24]),
      test_14: buildScoreRows([10, 14, 18, 22, 24, 28, 31, 35, 39]),
      test_13_14: buildScoreRows([16, 23, 27, 32, 37, 42, 47, 52, 57]),
      test_14_wrong: buildScoreRows([21, 18, 15, 13, 10, 8, 5, 2, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_UNDER_18),
  },
  '19_20': {
    columns: {
      test_1_2: buildScoreRows([5, 11, 18, 26, 34, 44, 55, 63, 70]),
      test_3: buildScoreRows([9, 12, 15, 19, 23, 26, 29, 32, 35]),
      test_4: buildScoreRows([10, 14, 17, 21, 24, 27, 29, 31, 33]),
      test_3_4: buildScoreRows([21, 29, 35, 41, 47, 53, 57, 62, 67]),
      test_5: buildScoreRows([1, 3, 5, 10, 17, 25, 30, 35, 39]),
      test_6: buildScoreRows([3, 9, 15, 21, 26, 32, 38, 44, 50]),
      test_5_6: buildScoreRows([6, 15, 24, 33, 43, 55, 67, 76, 84]),
      test_7: buildScoreRows([5, 7, 9, 13, 16, 20, 24, 28, 33]),
      test_8: buildScoreRows([null, 1, 4, 9, 14, 24, 33, 39, 40]),
      test_9: buildScoreRows([3, 8, 13, 18, 23, 27, 30, 33, 35]),
      test_10: buildScoreRows([2, 6, 10, 14, 18, 23, 28, 33, 38]),
      test_7_10: buildScoreRows([21, 31, 43, 55, 68, 85, 103, 121, 136]),
      test_11: buildScoreRows([6, 11, 15, 19, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([5, 10, 15, 20, 23, 27, 31, 34, 37]),
      test_11_12: buildScoreRows([12, 22, 32, 40, 47, 53, 59, 65, 70]),
      test_13: buildScoreRows([2, 4, 7, 9, 12, 15, 18, 21, 24]),
      test_14: buildScoreRows([10, 14, 18, 22, 25, 28, 32, 36, 40]),
      test_13_14: buildScoreRows([16, 23, 27, 32, 37, 42, 47, 53, 59]),
      test_14_wrong: buildScoreRows([21, 18, 15, 13, 10, 7, 4, 1, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_19_20),
  },
  '21_29': {
    columns: {
      test_1_2: buildScoreRows([3, 9, 18, 28, 37, 47, 58, 67, 75]),
      test_3: buildScoreRows([8, 11, 15, 19, 23, 26, 29, 33, 36]),
      test_4: buildScoreRows([9, 13, 16, 21, 24, 27, 29, 31, 34]),
      test_3_4: buildScoreRows([19, 27, 33, 40, 46, 53, 58, 64, 69]),
      test_5: buildScoreRows([1, 2, 5, 9, 17, 25, 31, 36, 40]),
      test_6: buildScoreRows([3, 7, 13, 19, 25, 32, 39, 45, 51]),
      test_5_6: buildScoreRows([3, 11, 20, 29, 39, 53, 67, 79, 87]),
      test_7: buildScoreRows([5, 7, 9, 13, 17, 20, 25, 30, 35]),
      test_8: buildScoreRows([null, 1, 3, 8, 13, 23, 32, 39, 40]),
      test_9: buildScoreRows([1, 7, 12, 18, 23, 27, 31, 33, 36]),
      test_10: buildScoreRows([1, 5, 9, 14, 18, 23, 28, 34, 39]),
      test_7_10: buildScoreRows([16, 27, 38, 50, 64, 83, 102, 123, 140]),
      test_11: buildScoreRows([5, 11, 15, 18, 22, 26, 29, 32, 35]),
      test_12: buildScoreRows([4, 9, 14, 19, 24, 28, 32, 35, 38]),
      test_11_12: buildScoreRows([7, 18, 29, 39, 46, 54, 61, 66, 72]),
      test_13: buildScoreRows([1, 3, 6, 9, 12, 15, 18, 21, 25]),
      test_14: buildScoreRows([9, 13, 18, 22, 24, 28, 32, 36, 40]),
      test_13_14: buildScoreRows([15, 22, 27, 32, 37, 42, 47, 54, 60]),
      test_14_wrong: buildScoreRows([21, 18, 15, 12, 9, 6, 3, 1, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_21_29),
  },
  '30_39': {
    columns: {
      test_1_2: buildScoreRows([1, 8, 18, 29, 39, 50, 61, 70, 77]),
      test_3: buildScoreRows([6, 10, 13, 17, 21, 25, 29, 32, 36]),
      test_4: buildScoreRows([8, 11, 15, 20, 23, 26, 29, 31, 34]),
      test_3_4: buildScoreRows([16, 23, 30, 37, 44, 51, 57, 63, 69]),
      test_5: buildScoreRows([null, 1, 4, 8, 15, 24, 31, 37, 40]),
      test_6: buildScoreRows([1, 4, 10, 17, 23, 29, 36, 42, 48]),
      test_5_6: buildScoreRows([1, 7, 16, 25, 35, 48, 63, 74, 84]),
      test_7: buildScoreRows([5, 7, 9, 12, 16, 21, 26, 31, 37]),
      test_8: buildScoreRows([null, null, 1, 7, 12, 22, 31, 38, 40]),
      test_9: buildScoreRows([1, 4, 10, 15, 21, 26, 30, 33, 35]),
      test_10: buildScoreRows([1, 2, 7, 12, 16, 21, 27, 33, 38]),
      test_7_10: buildScoreRows([11, 21, 32, 44, 57, 73, 90, 122, 140]),
      test_11: buildScoreRows([2, 9, 13, 17, 21, 25, 28, 32, 35]),
      test_12: buildScoreRows([2, 8, 13, 18, 23, 28, 32, 35, 38]),
      test_11_12: buildScoreRows([3, 12, 24, 34, 43, 51, 60, 67, 73]),
      test_13: buildScoreRows([1, 2, 5, 8, 11, 14, 18, 21, 24]),
      test_14: buildScoreRows([8, 12, 16, 20, 23, 27, 31, 35, 39]),
      test_13_14: buildScoreRows([13, 20, 25, 30, 35, 40, 45, 52, 59]),
      test_14_wrong: buildScoreRows([21, 17, 14, 11, 8, 5, 2, null, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_30_39),
  },
  '40_49': {
    columns: {
      test_1_2: buildScoreRows([1, 6, 17, 29, 40, 51, 62, 70, 77]),
      test_3: buildScoreRows([5, 9, 12, 16, 20, 24, 28, 31, 34]),
      test_4: buildScoreRows([6, 10, 14, 18, 22, 25, 28, 31, 34]),
      test_3_4: buildScoreRows([13, 20, 27, 34, 41, 48, 55, 61, 67]),
      test_5: buildScoreRows([null, 1, 3, 7, 14, 22, 30, 36, 40]),
      test_6: buildScoreRows([1, 2, 8, 14, 20, 26, 32, 38, 44]),
      test_5_6: buildScoreRows([1, 4, 13, 22, 32, 45, 58, 69, 80]),
      test_7: buildScoreRows([4, 6, 8, 11, 15, 19, 24, 29, 35]),
      test_8: buildScoreRows([null, null, 1, 5, 11, 20, 29, 36, 40]),
      test_9: buildScoreRows([1, 2, 7, 13, 18, 23, 28, 32, 35]),
      test_10: buildScoreRows([null, 1, 5, 10, 15, 20, 26, 32, 37]),
      test_7_10: buildScoreRows([6, 16, 26, 38, 50, 64, 79, 103, 123]),
      test_11: buildScoreRows([1, 6, 10, 14, 18, 23, 27, 31, 34]),
      test_12: buildScoreRows([1, 6, 11, 16, 21, 25, 30, 34, 38]),
      test_11_12: buildScoreRows([1, 9, 20, 29, 38, 45, 53, 61, 69]),
      test_13: buildScoreRows([null, 1, 4, 7, 10, 13, 16, 19, 23]),
      test_14: buildScoreRows([6, 10, 14, 18, 22, 26, 30, 34, 38]),
      test_13_14: buildScoreRows([11, 18, 23, 28, 32, 37, 43, 49, 56]),
      test_14_wrong: buildScoreRows([20, 16, 13, 10, 7, 4, 1, null, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_40_49),
  },
  '50_plus': {
    columns: {
      test_1_2: buildScoreRows([1, 4, 16, 29, 41, 52, 62, 70, 77]),
      test_3: buildScoreRows([4, 7, 11, 14, 18, 22, 25, 29, 32]),
      test_4: buildScoreRows([4, 8, 12, 16, 20, 24, 27, 30, 33]),
      test_3_4: buildScoreRows([8, 15, 22, 29, 37, 45, 51, 57, 63]),
      test_5: buildScoreRows([null, 1, 2, 5, 11, 18, 28, 34, 39]),
      test_6: buildScoreRows([null, 1, 6, 12, 18, 23, 28, 34, 39]),
      test_5_6: buildScoreRows([null, 1, 10, 19, 29, 41, 52, 63, 74]),
      test_7: buildScoreRows([3, 5, 7, 9, 12, 16, 21, 26, 32]),
      test_8: buildScoreRows([null, null, 1, 3, 9, 16, 24, 32, 39]),
      test_9: buildScoreRows([null, 1, 5, 9, 14, 19, 23, 26, 29]),
      test_10: buildScoreRows([null, 1, 2, 7, 12, 18, 23, 28, 34]),
      test_7_10: buildScoreRows([1, 9, 19, 29, 40, 52, 65, 76, 89]),
      test_11: buildScoreRows([1, 2, 6, 10, 15, 19, 24, 29, 33]),
      test_12: buildScoreRows([1, 2, 8, 13, 17, 21, 25, 30, 34]),
      test_11_12: buildScoreRows([1, 5, 15, 22, 29, 37, 44, 52, 59]),
      test_13: buildScoreRows([null, 1, 2, 5, 8, 11, 14, 17, 20]),
      test_14: buildScoreRows([4, 8, 12, 15, 19, 23, 28, 33, 37]),
      test_13_14: buildScoreRows([9, 15, 20, 25, 29, 33, 38, 43, 49]),
      test_14_wrong: buildScoreRows([18, 15, 11, 8, 5, 2, null, null, null]),
    },
    total: buildTotalRows(LPS_B_TOTAL_50_PLUS),
  },
};

/**
 * Zuordnung von T‑Wert‑Bereichen zu IQ‑Werten.
 * Diese Tabelle dient der groben Einordnung des Ergebnisses.
 */
export const LPS_B_IQ_BY_T_RANGES: Array<{ minT: number; maxT: number; iq: number }> = [
  { minT: 79, maxT: 80, iq: 145 },
  { minT: 77, maxT: 78, iq: 142 },
  { minT: 75, maxT: 76, iq: 139 },
  { minT: 73, maxT: 74, iq: 136 },
  { minT: 71, maxT: 72, iq: 133 },
  { minT: 69, maxT: 70, iq: 130 },
  { minT: 67, maxT: 68, iq: 127 },
  { minT: 65, maxT: 66, iq: 124 },
  { minT: 63, maxT: 64, iq: 121 },
  { minT: 61, maxT: 62, iq: 118 },
  { minT: 59, maxT: 60, iq: 115 },
  { minT: 57, maxT: 58, iq: 112 },
  { minT: 55, maxT: 56, iq: 109 },
  { minT: 53, maxT: 54, iq: 106 },
  { minT: 51, maxT: 52, iq: 103 },
  { minT: 49, maxT: 50, iq: 100 },
  { minT: 47, maxT: 48, iq: 97 },
  { minT: 45, maxT: 46, iq: 94 },
  { minT: 43, maxT: 44, iq: 91 },
  { minT: 41, maxT: 42, iq: 88 },
  { minT: 39, maxT: 40, iq: 85 },
  { minT: 37, maxT: 38, iq: 82 },
  { minT: 35, maxT: 36, iq: 79 },
  { minT: 33, maxT: 34, iq: 76 },
  { minT: 31, maxT: 32, iq: 73 },
  { minT: 29, maxT: 30, iq: 70 },
  { minT: 27, maxT: 28, iq: 67 },
  { minT: 25, maxT: 26, iq: 64 },
];
export type LpsBAgeGroupKey = 'under_18' | '19_20' | '21_29' | '30_39' | '40_49' | '50_plus';

export type LpsBScoreRow = {
  raw: number;
  t: number;
  c: number;
};

export type LpsBScoreRangeRow = {
  min: number;
  max: number;
  t: number;
  c: number;
};

export type LpsBTotalScoreRow = (LpsBScoreRow | LpsBScoreRangeRow) & { pr?: number };

export type LpsBScoreKey =
  | 'a'
  | 'b'
  | 'c'
  | 'd'
  | 'e'
  | 'f'
  | 'g'
  | 'h'
  | 'i'
  | 'j'
  | 'k'
  | 'l'
  | 'm'
  | 'n'
  | 'o'
  | 'p'
  | 'q'
  | 'r'
  | 's';

export type LpsBScoreTables = Record<
  LpsBAgeGroupKey,
  {
    columns: Partial<Record<LpsBScoreKey, Array<LpsBScoreRow | LpsBScoreRangeRow>>>;
    total: LpsBTotalScoreRow[];
  }
>;

export const LPS_B_AGE_GROUP_LABELS: Record<LpsBAgeGroupKey, string> = {
  under_18: 'Bis 18',
  '19_20': '19–20',
  '21_29': '21–29',
  '30_39': '30–39',
  '40_49': '40–49',
  '50_plus': '50+',
};

export const LPS_B_SCORE_TABLES: LpsBScoreTables = {
  under_18: { columns: {}, total: [] },
  '19_20': { columns: {}, total: [] },
  '21_29': { columns: {}, total: [] },
  '30_39': { columns: {}, total: [] },
  '40_49': { columns: {}, total: [] },
  '50_plus': { columns: {}, total: [] },
};

export const LPS_B_IQ_BY_PR_RANGES: Array<{ minPr: number; maxPr: number; iq: number }> = [];

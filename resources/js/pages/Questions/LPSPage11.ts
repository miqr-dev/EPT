export type LpsPage11Entry = {
  id: number;
  value: string;
  correctIndices?: number[];
};

export type LpsPage11Row = {
  id: number;
  column13: string;
  column14: string;
};

export type LpsPage11Solution = {
  col13?: number[];
  col14?: number[];
};

export type LpsPage11Dataset = {
  rows: LpsPage11Row[];
  solutions: LpsPage11Solution[];
};

const LPS_PAGE11_COLUMN14_VALUES = `
2 1 0 1 1 9
1 0 1 1 5 1
0 9 0 2 1 0
7 2 4 0 0 2
0 6 2 1 5 4
6 3 4 8 9 2
4 2 4 1 5 1
7 6 0 4 2 3
9 1 5 8 3 4
6 4 0 2 3 8
2 3 6 3 4 1
9 5 8 0 5 3
6 8 4 8 2 5
1 3 0 5 9 8
S 2 6 0 7 3
7 8 7 3 9 6
7 2 9 6 5 4
0 3 7 8 3 1
9 6 5 2 8 6
B T 3 6 4 9
M 7 0 8 A 5
S 6 4 L 2 8
4 9 N 3 Q 6
2 7 0 7 8 4
0 6 9 5 2 1
5 8 0 3 6 8
9 1 9 4 2 5
7 7 4 0 3 4
8 4 5 2 4 0
0 2 4 9 5 2
4 6 1 0 7 0 5 2
0 9 4 7 2 S 3 0
7 5 6 5 1 G 2 4
S 0 5 1 9 T 3 7
M L 6 2 0 8 4 9
2 9 0 4 6 2 5 8
7 4 1 8 3 W 1 T
N 7 5 4 6 9 7 3
K S 7 U 1 2 7
3 9 3 8 6 8 4
N Z 0 V 5 1 Z 3
S 2 M 8 T 9 9 X
R 8 N 1 6 3 S 5
Q P T 8 3 9 B A
6 M 7 5 3 1 8 5
B T A C L K 6 3
7 8 1 4 9 5 0 8
S 7 9 U V 2 3 9
M K 7 9 1 8 6 7
S 4 R L 6 9 M 9
Q N S 0 8 5 1 3
A 6 B T 9 7 S 6
A 0 7 S 1 M 2 7
V K L 0 6 N W S
0 F 1 H 2 3 5 9
V L K E 7 8 6 4
S 2 3 T W 0 0 5
6 M 0 7 1 0 5 3
S 1 5 T 0 6 2 1
9 7 3 9 6 8 5 8
`
  .trim()
  .split('\n')
  .map((row) => row.trim());

const LPS_PAGE11_COLUMN14_CORRECT: Record<number, number[]> = {
  1: [5],
  3: [1],
  5: [3],
  7: [5],
  9: [0],
  10: [2],
  11: [4],
  12: [2],
  15: [1, 4],
  17: [3],
  18: [5],
  19: [2],
  20: [4],
  22: [0],
  23: [2],
  25: [0],
  26: [2],
  28: [3],
  30: [2],
  31: [5],
  32: [2, 7],
  33: [5],
  35: [2],
  36: [6],
  37: [1, 5],
  38: [3],
  39: [5],
  40: [0, 3],
  44: [4],
  45: [6],
  48: [2],
  49: [4],
  52: [2],
  53: [4],
  55: [7],
  58: [6],
};

const LPS_PAGE11_COLUMN13_VALUES = `
2 1 0 1 1 2
1 0 1 1 5 1
0 9 0 2 1 0
7 2 4 0 0 2
0 6 2 1 5 4
6 3 4 8 9 2
4 2 4 1 5 4
7 6 0 4 2 3
9 1 5 8 3 4
6 4 0 2 3 8
2 3 6 3 4 1
9 5 8 0 5 3
6 8 4 8 2 5
1 3 0 5 9 8
S 7 6 0 2 3
7 8 7 3 9 6
7 2 9 6 5 4
0 3 7 8 3 1
9 6 5 2 8 6
B T 3 6 4 9
M 7 0 8 A 5
S 6 4 L 2 8
4 9 N 3 Q 6
2 7 0 7 8 4
4 6 9 5 2 1
5 8 0 3 6 8
9 1 9 4 2 5
7 7 4 0 3 4
8 4 5 2 4 0
0 2 4 9 5 2
4 6 1 0 7 8 5 2
0 9 4 7 2 S 3 0
7 5 6 5 1 G 2 4
S 0 5 1 9 T 3 7
M L 6 2 0 8 4 9
2 9 0 4 6 2 5 8
7 3 1 8 3 2 1 T
N 7 5 4 6 9 7 3
K S 7 U 1 W 1 7
7 3 9 3 8 6 8 4
N 7 0 V 5 1 Z 3
S 2 M 8 T 9 9 X
R 8 N 1 6 3 S 5
Q P T 8 3 9 B A
6 M 7 5 3 1 8 5
B T A C L K 6 3
7 8 1 4 9 5 0 8
S 7 9 U V 2 3 9
M K 7 9 1 8 6 7
S 4 R L 6 9 M 9
Q N S 0 8 5 1 3
A 6 B T 9 7 S 6
A 0 7 S 1 M 2 7
V K L 0 6 N W S
0 F 1 H 2 3 5 9
V L K E 7 8 6 4
S 2 3 T W 0 0 5
6 M 0 7 1 0 5 3
S 1 5 T 0 6 2 1
9 7 3 9 6 8 5 8
`
  .trim()
  .split('\n')
  .map((row) => row.trim());

const LPS_PAGE11_COLUMN13_CORRECT: Record<number, number[]> = {
  3: [4],
  5: [0],
  7: [1],
  9: [5],
  14: [1, 3],
  16: [1, 5],
  19: [0],
  21: [1],
  22: [4],
  24: [2, 5],
  26: [4],
  27: [1],
  28: [4],
  29: [0],
  30: [4],
  31: [0],
  32: [4],
  34: [1, 4, 7],
  41: [5, 7],
  43: [1, 7],
  45: [0, 2],
  47: [4],
  50: [1],
  53: [6],
  55: [0],
  56: [4],
  57: [2],
  58: [0, 4],
  59: [2],
  60: [0, 5],
};

function splitTokens(value: string) {
  if (!value) return [];
  return value.trim().split(/\s+/);
}

const LPS_PAGE11_COLUMN14_B: LpsPage11Entry[] = LPS_PAGE11_COLUMN14_VALUES.map((value, idx) => ({
  id: idx + 1,
  value,
  correctIndices: LPS_PAGE11_COLUMN14_CORRECT[idx + 1] ?? [],
}));

const LPS_PAGE11_COLUMN13_B: LpsPage11Entry[] = LPS_PAGE11_COLUMN13_VALUES.map((value, idx) => ({
  id: idx + 1,
  value,
  correctIndices: LPS_PAGE11_COLUMN13_CORRECT[idx + 1] ?? [],
}));

function buildRow(rowIdx: number, column13: LpsPage11Entry[], column14: LpsPage11Entry[]): LpsPage11Row {
  const col13 = column13[rowIdx];
  const col14 = column14[rowIdx];
  const fallbackId = rowIdx + 1;

  return {
    id: col14?.id ?? col13?.id ?? fallbackId,
    column13: col13?.value ?? '',
    column14: col14?.value ?? '',
  };
}

function buildRows(column13: LpsPage11Entry[], column14: LpsPage11Entry[]) {
  const rowCount = Math.max(column13.length, column14.length);
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column13, column14));
}

function extractColumn14Solution(entry?: LpsPage11Entry): number[] {
  if (!entry?.correctIndices?.length) return [];
  const tokens = splitTokens(entry.value);
  return entry.correctIndices.filter((idx) => idx >= 0 && idx < tokens.length);
}

function buildSolutions(column13: LpsPage11Entry[]) {
  return buildRows(column13, LPS_PAGE11_COLUMN14_B).map((_, idx) => ({
    col13: extractColumn14Solution(column13[idx]),
    col14: extractColumn14Solution(LPS_PAGE11_COLUMN14_B[idx]),
  }));
}

export const LPS_PAGE11_ROWS_B = buildRows(LPS_PAGE11_COLUMN13_B, LPS_PAGE11_COLUMN14_B);
export const LPS_PAGE11_SOLUTIONS_B = buildSolutions(LPS_PAGE11_COLUMN13_B);

export function getLpsPage11Dataset(testName?: string): LpsPage11Dataset {
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE11_ROWS_B, solutions: LPS_PAGE11_SOLUTIONS_B };
  }

  return { rows: [], solutions: [] };
}

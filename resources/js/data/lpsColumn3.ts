export type LpsColumn3Path = {
  d: string;
  transform?: string;
};

export type LpsColumn3Row = {
  id: number;
  viewBox: string;
  width: number;
  height: number;
  paths: LpsColumn3Path[];
  /**
   * Zero-based index of the correct path for scoring.
   */
  correctIndex: number;
};

// The real dataset is intentionally kept in this dedicated module so it can be
// code-split away from the page logic. Populate this array with the full
// column 3 shapes as they become available.
export const lpsColumn3Rows: LpsColumn3Row[] = [];

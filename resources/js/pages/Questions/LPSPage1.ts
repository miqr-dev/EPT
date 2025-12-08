export type LpsColumn3Option = { id: string; src?: string; pathData?: string; transform?: string };

export type LpsPage1Row = {
  id: number;
  column1: string;
  column2: string;
  column3?: LpsColumn3Option[];
  column3SvgMeta?: { viewBox: string; width: number; height: number };
  column4: string;
  column5: string;
};

export type LpsColumnEntry = {
  id: number;
  word: string;
  /**
   * Zero-based index of the correct character within the word.
   * When omitted, the entry will not contribute to scoring.
   */
  correctIndex?: number;
};

export type LpsPage1Solution = {
  /**
   * Zero-based indices that represent the correct characters for each column entry.
   * Selecting one of these indices awards a point for the row/column.
   */
  col1?: number[];
  col2?: number[];
  col3?: number[];
  col4?: number[];
  col5?: number[];
};

type LpsColumn3Row = {
  id: number;
  options: LpsColumn3Option[];
  correctIndex?: number;
  svgMeta?: { viewBox: string; width: number; height: number };
};

export type LpsDataset = {
  rows: LpsPage1Row[];
  solutions: LpsPage1Solution[];
};

export const LPS_PAGE1_COLUMN1: LpsColumnEntry[] = [
  { id: 1, word: 'Auvzug', correctIndex: 0 },
  { id: 2, word: 'Freute', correctIndex: 0 },
  { id: 3, word: 'Alaske', correctIndex: 0 },
  { id: 4, word: 'Bezirg', correctIndex: 0 },
  { id: 5, word: 'Ardist', correctIndex: 0 },
  { id: 6, word: 'Waknis', correctIndex: 0 },
  { id: 7, word: 'Gewaih', correctIndex: 0 },
  { id: 8, word: 'Magmet', correctIndex: 0 },
  { id: 9, word: 'Legiom', correctIndex: 0 },
  { id: 10, word: 'Jasmim', correctIndex: 0 },
  { id: 11, word: 'Tiskus', correctIndex: 0 },
  { id: 12, word: 'Panema', correctIndex: 0 },
  { id: 13, word: 'Heliun', correctIndex: 0 },
  { id: 14, word: 'Ratium', correctIndex: 0 },
  { id: 15, word: 'Vugger', correctIndex: 0 },
  { id: 16, word: 'Etison', correctIndex: 0 },
  { id: 17, word: 'Vright', correctIndex: 0 },
  { id: 18, word: 'Prokat', correctIndex: 0 },
  { id: 19, word: 'Attilo', correctIndex: 0 },
  { id: 20, word: 'Dnjebr', correctIndex: 0 },
  { id: 21, word: 'Toulom', correctIndex: 0 },
  { id: 22, word: 'Amatom', correctIndex: 0 },
  { id: 23, word: 'Namtes', correctIndex: 0 },
  { id: 24, word: 'Naffia', correctIndex: 0 },
  { id: 25, word: 'Garies', correctIndex: 0 },
  { id: 26, word: 'Virkil', correctIndex: 0 },
  { id: 27, word: 'Grachd', correctIndex: 0 },
  { id: 28, word: 'Armika', correctIndex: 0 },
  { id: 29, word: 'Mohavk', correctIndex: 0 },
  { id: 30, word: 'Hamgar', correctIndex: 0 },
  { id: 31, word: 'Prodon', correctIndex: 0 },
  { id: 32, word: 'Emplem', correctIndex: 0 },
  { id: 33, word: 'Anemie', correctIndex: 0 },
  { id: 34, word: 'Osires', correctIndex: 0 },
  { id: 35, word: 'Sharge', correctIndex: 0 },
  { id: 36, word: 'Druite', correctIndex: 0 },
  { id: 37, word: 'Behain', correctIndex: 0 },
  { id: 38, word: 'Kaulin', correctIndex: 0 },
  { id: 39, word: 'Läsiom', correctIndex: 0 },
  { id: 40, word: 'Gounot', correctIndex: 0 }
];

export const LPS_PAGE1_COLUMN2: LpsColumnEntry[] = [
  { id: 1, word: 'Heimad', correctIndex: 0 },
  { id: 2, word: 'Herink', correctIndex: 0 },
  { id: 3, word: 'Expord', correctIndex: 0 },
  { id: 4, word: 'Pulwer', correctIndex: 0 },
  { id: 5, word: 'Jeguar', correctIndex: 0 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 0 },
  { id: 9, word: 'Madred', correctIndex: 0 },
  { id: 10, word: 'Rikrut', correctIndex: 0 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 0 },
  { id: 14, word: 'Ottawe', correctIndex: 0 },
  { id: 15, word: 'Spagad', correctIndex: 0 },
  { id: 16, word: 'Dewise', correctIndex: 0 },
  { id: 17, word: 'Riflex', correctIndex: 0 },
  { id: 18, word: 'Bombai', correctIndex: 0 },
  { id: 19, word: 'Tegris', correctIndex: 0 },
  { id: 20, word: 'Nimphe', correctIndex: 0 },
  { id: 21, word: 'Delfhi', correctIndex: 0 },
  { id: 22, word: 'Beuxit', correctIndex: 0 },
  { id: 23, word: 'Okolar', correctIndex: 0 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 0 },
  { id: 26, word: 'Netrat', correctIndex: 0 },
  { id: 27, word: 'Smirna', correctIndex: 0 },
  { id: 28, word: 'Akelai', correctIndex: 0 },
  { id: 29, word: 'Budgot', correctIndex: 0 },
  { id: 30, word: 'Amelfi', correctIndex: 0 },
  { id: 31, word: 'Alippo', correctIndex: 0 },
  { id: 32, word: 'Komdur', correctIndex: 0 },
  { id: 33, word: 'Bowist', correctIndex: 0 },
  { id: 34, word: 'Prokob', correctIndex: 0 },
  { id: 35, word: 'Honwed', correctIndex: 0 },
  { id: 36, word: 'Laudom', correctIndex: 0 },
  { id: 37, word: 'Pantur', correctIndex: 0 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 0 },
  { id: 40, word: 'Anubes', correctIndex: 0 },
];

export const LPS_PAGE1_COLUMN4: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 2, word: 'Herink', correctIndex: 0 },
  { id: 3, word: 'Expord', correctIndex: 0 },
  { id: 4, word: 'Pulwer', correctIndex: 0 },
  { id: 5, word: 'Jeguar', correctIndex: 0 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 0 },
  { id: 9, word: 'Madred', correctIndex: 0 },
  { id: 10, word: 'Rikrut', correctIndex: 0 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 0 },
  { id: 14, word: 'Ottawe', correctIndex: 0 },
  { id: 15, word: 'Spagad', correctIndex: 0 },
  { id: 16, word: 'Dewise', correctIndex: 0 },
  { id: 17, word: 'Riflex', correctIndex: 0 },
  { id: 18, word: 'Bombai', correctIndex: 0 },
  { id: 19, word: 'Tegris', correctIndex: 0 },
  { id: 20, word: 'Nimphe', correctIndex: 0 },
  { id: 21, word: 'Delfhi', correctIndex: 0 },
  { id: 22, word: 'Beuxit', correctIndex: 0 },
  { id: 23, word: 'Okolar', correctIndex: 0 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 0 },
  { id: 26, word: 'Netrat', correctIndex: 0 },
  { id: 27, word: 'Smirna', correctIndex: 0 },
  { id: 28, word: 'Akelai', correctIndex: 0 },
  { id: 29, word: 'Budgot', correctIndex: 0 },
  { id: 30, word: 'Amelfi', correctIndex: 0 },
  { id: 31, word: 'Alippo', correctIndex: 0 },
  { id: 32, word: 'Komdur', correctIndex: 0 },
  { id: 33, word: 'Bowist', correctIndex: 0 },
  { id: 34, word: 'Prokob', correctIndex: 0 },
  { id: 35, word: 'Honwed', correctIndex: 0 },
  { id: 36, word: 'Laudom', correctIndex: 0 },
  { id: 37, word: 'Pantur', correctIndex: 0 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 0 },
  { id: 40, word: 'Anubes', correctIndex: 0 },
];

export const LPS_PAGE1_COLUMN5: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 2, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 3, word: 'xxxxxxxox', correctIndex: 0 },
  { id: 4, word: 'xxxxxxxox', correctIndex: 0 },
];

const LPS_PAGE1_COLUMN3_B_ROW1_SHAPES = [
  `
        M 269.4023 37.3704
        A 1.79 1.79 0.0 0 1 267.6092 39.1573
        L 236.3292 39.1027
        A 1.79 1.79 0.0 0 1 234.5423 37.3096
        L 234.5977 5.5696
        A 1.79 1.79 0.0 0 1 236.3908 3.7827
        L 267.6708 3.8373
        A 1.79 1.79 0.0 0 1 269.4577 5.6304
        L 269.4023 37.3704
        Z
        M 250.3643 8.9124
        A 1.40 1.40 0.0 0 0 248.9692 7.5075
        L 238.9892 7.4727
        A 1.40 1.40 0.0 0 0 237.5844 8.8678
        L 237.4957 34.2676
        A 1.40 1.40 0.0 0 0 238.8908 35.6725
        L 248.8708 35.7073
        A 1.40 1.40 0.0 0 0 250.2756 34.3122
        L 250.3643 8.9124
        Z
        M 266.5148 9.2649
        A 1.63 1.63 0.0 0 0 264.8933 7.6264
        L 254.8935 7.5740
        A 1.63 1.63 0.0 0 0 253.2550 9.1955
        L 253.1252 33.9751
        A 1.63 1.63 0.0 0 0 254.7467 35.6136
        L 264.7465 35.6660
        A 1.63 1.63 0.0 0 0 266.3850 34.0445
        L 266.5148 9.2649
        Z
      `,
  `
        M 6.01 21.34
        L 23.21 4.14
        A 1.83 1.83 0.0 0 1 25.79 4.14
        L 43.46 21.81
        A 1.83 1.83 0.0 0 1 43.46 24.39
        L 26.26 41.59
        A 1.83 1.83 0.0 0 1 23.68 41.59
        L 6.01 23.92
        A 1.83 1.83 0.0 0 1 6.01 21.34
        Z
        M 24.6348 8.4914
        A 0.26 0.26 0.0 0 0 24.2671 8.4882
        L 10.0307 22.4783
        A 0.26 0.26 0.0 0 0 10.0275 22.8460
        L 15.9852 28.9086
        A 0.26 0.26 0.0 0 0 16.3529 28.9118
        L 30.5893 14.9217
        A 0.26 0.26 0.0 0 0 30.5925 14.5540
        L 24.6348 8.4914
        Z
        M 24.44 37.11
        A 0.89 0.89 0.0 0 0 25.70 37.11
        L 39.01 23.81
        A 0.89 0.89 0.0 0 0 39.01 22.55
        L 34.07 17.61
        A 0.89 0.89 0.0 0 0 32.81 17.61
        L 19.51 30.92
        A 0.89 0.89 0.0 0 0 19.51 32.18
        L 24.44 37.11
        Z
      `,
  `
        M 325.98 39.07
        L 291.25 39.07
        A 0.63 0.63 0.0 0 1 290.62 38.44
        L 290.62 4.49
        A 0.63 0.63 0.0 0 1 291.25 3.86
        L 325.98 3.86
        A 0.63 0.63 0.0 0 1 326.61 4.49
        L 326.61 38.44
        A 0.63 0.63 0.0 0 1 325.98 39.07
        Z
        M 307.4134 9.0444
        A 1.53 1.53 0.0 0 0 305.8887 7.5091
        L 294.9888 7.4711
        A 1.53 1.53 0.0 0 0 293.4535 8.9957
        L 293.3666 33.8756
        A 1.53 1.53 0.0 0 0 294.8913 35.4109
        L 305.7912 35.4489
        A 1.53 1.53 0.0 0 0 307.3265 33.9243
        L 307.4134 9.0444
        Z
        M 323.7953 8.5536
        A 1.11 1.11 0.0 0 0 322.6892 7.4398
        L 311.4093 7.4004
        A 1.11 1.11 0.0 0 0 310.2954 8.5065
        L 310.2047 34.4864
        A 1.11 1.11 0.0 0 0 311.3108 35.6002
        L 322.5907 35.6396
        A 1.11 1.11 0.0 0 0 323.7046 34.5335
        L 323.7953 8.5536
        Z
      `,
  `
        M 98.2516 38.3806
        A 1.75 1.75 0.0 0 1 96.4986 40.1276
        L 64.8786 40.0724
        A 1.75 1.75 0.0 0 1 63.1317 38.3193
        L 63.1884 5.8394
        A 1.75 1.75 0.0 0 1 64.9414 4.0924
        L 96.5614 4.1476
        A 1.75 1.75 0.0 0 1 98.3083 5.9007
        L 98.2516 38.3806
        Z
        M 95.3888 9.9690
        A 1.86 1.86 0.0 0 0 93.5255 8.1123
        L 84.6456 8.1278
        A 1.86 1.86 0.0 0 0 82.7888 9.9910
        L 82.8312 34.2910
        A 1.86 1.86 0.0 0 0 84.6945 36.1477
        L 93.5744 36.1322
        A 1.86 1.86 0.0 0 0 95.4312 34.2690
        L 95.3888 9.9690
        Z
        M 79.8516 9.7721
        A 1.72 1.72 0.0 0 0 78.1346 8.0491
        L 67.7547 8.0310
        A 1.72 1.72 0.0 0 0 66.0317 9.7480
        L 65.9884 34.5479
        A 1.72 1.72 0.0 0 0 67.7054 36.2709
        L 78.0853 36.2890
        A 1.72 1.72 0.0 0 0 79.8083 34.5720
        L 79.8516 9.7721
        Z
      `,
  `
        M 177.12 37.78
        L 177.12 5.81
        A 1.67 1.67 0.0 0 1 178.79 4.14
        L 212.11 4.14
        A 1.67 1.67 0.0 0 1 213.78 5.81
        L 213.78 37.78
        A 1.67 1.67 0.0 0 1 212.11 39.45
        L 178.79 39.45
        A 1.67 1.67 0.0 0 1 177.12 37.78
        Z
        M 210.92 9.69
        A 2.01 2.01 0.0 0 0 208.91 7.68
        L 198.63 7.68
        A 2.01 2.01 0.0 0 0 196.62 9.69
        L 196.62 33.79
        A 2.01 2.01 0.0 0 0 198.63 35.80
        L 208.91 35.80
        A 2.01 2.01 0.0 0 0 210.92 33.79
        L 210.92 9.69
        Z
        M 193.6649 9.5159
        A 1.68 1.68 0.0 0 0 191.9938 7.8272
        L 181.6939 7.7732
        A 1.68 1.68 0.0 0 0 180.0051 9.4444
        L 179.8751 34.2841
        A 1.68 1.68 0.0 0 0 181.5462 35.9728
        L 191.8461 36.0268
        A 1.68 1.68 0.0 0 0 193.5349 34.3556
        L 193.6649 9.5159
        Z
      `,
  `
        M 156.1382 38.1279
        A 1.55 1.55 0.0 0 1 154.5909 39.6806
        L 120.8909 39.7394
        A 1.55 1.55 0.0 0 1 119.3382 38.1921
        L 119.2818 5.8721
        A 1.55 1.55 0.0 0 1 120.8291 4.3194
        L 154.5291 4.2606
        A 1.55 1.55 0.0 0 1 156.0818 5.8079
        L 156.1382 38.1279
        Z
        M 136.4539 9.5651
        A 1.52 1.52 0.0 0 0 134.9393 8.0398
        L 123.6593 8.0004
        A 1.52 1.52 0.0 0 0 122.1340 9.5151
        L 122.0461 34.7149
        A 1.52 1.52 0.0 0 0 123.5607 36.2402
        L 134.8407 36.2796
        A 1.52 1.52 0.0 0 0 136.3660 34.7649
        L 136.4539 9.5651
        Z
        M 152.9325 9.1650
        A 1.22 1.22 0.0 0 0 151.7061 7.9514
        L 140.7063 8.0090
        A 1.22 1.22 0.0 0 0 139.4927 9.2354
        L 139.6275 34.9750
        A 1.22 1.22 0.0 0 0 140.8539 36.1886
        L 151.8537 36.1310
        A 1.22 1.22 0.0 0 0 153.0673 34.9046
        L 152.9325 9.1650
        Z
      `,
  `
        M 348.89 37.81
        L 348.89 6.16
        A 1.74 1.74 0.0 0 1 350.63 4.42
        L 382.73 4.42
        A 1.74 1.74 0.0 0 1 384.47 6.16
        L 384.47 37.81
        A 1.74 1.74 0.0 0 1 382.73 39.55
        L 350.63 39.55
        A 1.74 1.74 0.0 0 1 348.89 37.81
        Z
        M 351.93 9.23
        L 351.89 34.81
        A 1.21 1.21 0.0 0 0 353.10 36.02
        L 362.83 36.04
        A 2.78 1.97 0.1 0 0 365.61 34.07
        L 365.65 10.01
        A 2.78 1.97 0.1 0 0 362.87 8.04
        L 353.14 8.02
        A 1.21 1.21 0.0 0 0 351.93 9.23
        Z
        M 381.6842 9.6532
        A 1.15 1.15 0.0 0 0 380.5382 8.4992
        L 369.5983 8.4610
        A 1.15 1.15 0.0 0 0 368.4443 9.6070
        L 368.3558 34.9668
        A 1.15 1.15 0.0 0 0 369.5018 36.1208
        L 380.4417 36.1590
        A 1.15 1.15 0.0 0 0 381.5957 35.0130
        L 381.6842 9.6532
        Z
      `,
  `
        M 441.6722 38.6611
        A 1.74 1.74 0.0 0 1 439.9291 40.3981
        L 407.7492 40.3419
        A 1.74 1.74 0.0 0 1 406.0122 38.5989
        L 406.0678 6.7189
        A 1.74 1.74 0.0 0 1 407.8109 4.9819
        L 439.9908 5.0381
        A 1.74 1.74 0.0 0 1 441.7278 6.7811
        L 441.6722 38.6611
        Z
        M 409.50 9.27
        Q 408.96 13.91 408.95 15.25
        Q 408.83 28.28 409.02 35.50
        A 0.95 0.95 0.0 0 0 409.97 36.42
        L 420.69 36.42
        A 1.16 1.16 0.0 0 0 421.81 35.56
        C 422.71 32.18 421.85 29.03 422.15 25.84
        Q 422.53 21.87 422.24 12.35
        Q 422.17 9.94 421.47 9.43
        C 419.37 7.88 417.62 9.05 416.07 10.80
        A 1.13 1.12 52.8 0 1 414.21 10.54
        Q 413.15 8.35 410.11 8.67
        A 0.68 0.67 -89.5 0 0 409.50 9.27
        Z
        M 438.7321 10.7353
        A 1.91 1.91 0.0 0 0 436.8321 8.8154
        L 427.2122 8.7650
        A 1.91 1.91 0.0 0 0 425.2922 10.6650
        L 425.1679 34.4047
        A 1.91 1.91 0.0 0 0 427.0679 36.3246
        L 436.6878 36.3750
        A 1.91 1.91 0.0 0 0 438.6078 34.4750
        L 438.7321 10.7353
        Z
      `,
];

const LPS_PAGE1_COLUMN3_B_ROW3_SHAPES = [
  `
        M 42.3723 41.5322
        A 1.64 1.64 0.0 0 1 40.7294 43.1693
        L 7.0694 43.1106
        A 1.64 1.64 0.0 0 1 5.4323 41.4677
        L 5.4877 9.7078
        A 1.64 1.64 0.0 0 1 7.1306 8.0707
        L 40.7906 8.1294
        A 1.64 1.64 0.0 0 1 42.4277 9.7723
        L 42.3723 41.5322
        Z
        M 39.55 13.47
        A 1.62 1.62 0.0 0 0 37.93 11.85
        L 10.01 11.85
        A 1.62 1.62 0.0 0 0 8.39 13.47
        L 8.39 37.91
        A 1.62 1.62 0.0 0 0 10.01 39.53
        L 37.93 39.53
        A 1.62 1.62 0.0 0 0 39.55 37.91
        L 39.55 13.47
        Z
      `,
      `
      M 98.0866 41.7788 A 1.24 1.24 0 0 1 96.851 43.0232 L 64.3512 43.1366 A 1.24 1.24 0 0 1 63.1068 41.901 L 62.9934 9.4012 A 1.24 1.24 0 0 1 64.229 8.1568 L 96.7288 8.0434 A 1.24 1.24 0 0 1 97.9732 9.279 L 98.0866 41.7788 Z M 95.054 13.0137 A 1.42 1.42 0 0 0 93.6266 11.6012 L 67.2669 11.7392 A 1.42 1.42 0 0 0 65.8544 13.1666 L 65.986 38.3063 A 1.42 1.42 0 0 0 67.4134 39.7188 L 93.7731 39.5808 A 1.42 1.42 0 0 0 95.1856 38.1534 L 95.054 13.0137 Z
      `,
      `M 156.06 41.78 A 1.34 1.34 0 0 1 154.72 43.12 L 120.52 43.12 A 1.34 1.34 0 0 1 119.18 41.78 L 119.18 9.52 A 1.34 1.34 0 0 1 120.52 8.18 L 154.72 8.18 A 1.34 1.34 0 0 1 156.06 9.52 L 156.06 41.78 Z M 153.2329 13.4147 A 1.59 1.59 0 0 0 151.6484 11.8191 L 123.5486 11.721 A 1.59 1.59 0 0 0 121.9531 13.3055 L 121.8671 37.9253 A 1.59 1.59 0 0 0 123.4516 39.5209 L 151.5514 39.619 A 1.59 1.59 0 0 0 153.1469 38.0345 L 153.2329 13.4147 Z`,
      `M 213.4653 41.276 A 1.73 1.73 0 0 1 211.7413 43.012 L 178.5815 43.1278 A 1.73 1.73 0 0 1 176.8455 41.4038 L 176.7347 9.684 A 1.73 1.73 0 0 1 178.4587 7.948 L 211.6185 7.8322 A 1.73 1.73 0 0 1 213.3545 9.5562 L 213.4653 41.276 Z M 210.63 12.81 A 1.29 1.29 0 0 0 209.34 11.52 L 181.00 11.52 A 1.29 1.29 0 0 0 179.71 12.81 L 179.71 38.26 A 1.29 1.29 0 0 0 181.00 39.55 L 209.34 39.55 A 1.29 1.29 0 0 0 210.63 38.26 L 210.63 12.81 Z`,
      `M 269.2012 41.7703 A 1.13 1.13 0 0 1 268.0693 42.8983 L 235.5693 42.8416 A 1.13 1.13 0 0 1 234.4413 41.7096 L 234.4988 8.7897 A 1.13 1.13 0 0 1 235.6307 7.6617 L 268.1307 7.7184 A 1.13 1.13 0 0 1 269.2587 8.8504 L 269.2012 41.7703 Z M 266.2924 12.5053 A 1.23 1.23 0 0 0 265.0646 11.2731 L 238.5646 11.2269 A 1.23 1.23 0 0 0 237.3325 12.4547 L 237.2876 38.1947 A 1.23 1.23 0 0 0 238.5154 39.4269 L 265.0154 39.4731 A 1.23 1.23 0 0 0 266.2475 38.2453 L 266.2924 12.5053 Z`,
      `M 326.4384 41.3385 A 1.43 1.43 0 0 1 325.0109 42.771 L 291.8109 42.8289 A 1.43 1.43 0 0 1 290.3784 41.4014 L 290.3216 8.8615 A 1.43 1.43 0 0 1 291.7491 7.429 L 324.9491 7.3711 A 1.43 1.43 0 0 1 326.3816 8.7986 L 326.4384 41.3385 Z M 323.5576 12.9372 A 1.85 1.85 0 0 0 321.7011 11.0937 L 295.1013 11.1865 A 1.85 1.85 0 0 0 293.2578 13.043 L 293.3424 37.3028 A 1.85 1.85 0 0 0 295.1989 39.1463 L 321.7987 39.0535 A 1.85 1.85 0 0 0 323.6422 37.197 L 323.5576 12.9372 Z`,
      `M 384.2911 41.7612 A 1.14 1.14 0 0 1 383.1492 42.8992 L 349.6492 42.8407 A 1.14 1.14 0 0 1 348.5112 41.6988 L 348.5689 8.6588 A 1.14 1.14 0 0 1 349.7108 7.5208 L 383.2108 7.5793 A 1.14 1.14 0 0 1 384.3488 8.7212 L 384.2911 41.7612 Z M 381.6919 12.7064 A 1.42 1.42 0 0 0 380.2744 11.2839 L 352.9145 11.2361 A 1.42 1.42 0 0 0 351.492 12.6537 L 351.4481 37.8136 A 1.42 1.42 0 0 0 352.8656 39.2361 L 380.2255 39.2839 A 1.42 1.42 0 0 0 381.648 37.8663 L 381.6919 12.7064 Z`,
      `M 440.9968 25.3109 A 2.28 2.28 0 0 1 440.9799 28.5353 L 425.3707 43.9818 A 2.28 2.28 0 0 1 422.1464 43.9649 L 405.7432 27.3891 A 2.28 2.28 0 0 1 405.7601 24.1647 L 421.3693 8.7182 A 2.28 2.28 0 0 1 424.5936 8.7351 L 440.9968 25.3109 Z M 436.34 28.41 A 2.11 2.11 0 0 0 436.34 25.43 L 424.37 13.46 A 2.11 2.11 0 0 0 421.39 13.46 L 410.50 24.35 A 2.11 2.11 0 0 0 410.50 27.33 L 422.48 39.30 A 2.11 2.11 0 0 0 425.46 39.30 L 436.34 28.41 Z`,
];

const LPS_PAGE1_COLUMN3_B: LpsColumn3Row[] = [
  {
    id: 1,
    options: LPS_PAGE1_COLUMN3_B_ROW1_SHAPES.map((pathData, idx) => ({
      id: `lps-b-r1-shape${idx + 1}`,
      pathData,
    })),
    correctIndex: 0,
    svgMeta: { viewBox: '0 0 447 43', width: 447, height: 43 },
  },
  {
    id: 3,
    options: LPS_PAGE1_COLUMN3_B_ROW3_SHAPES.map((pathData, idx) => ({
      id: `lps-b-r3-shape${idx + 1}`,
      pathData,
    })),
    correctIndex: 7,
    svgMeta: { viewBox: '0 0 447 43', width: 446, height: 54 },
  },
];

function buildRow(
  rowIdx: number,
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
): LpsPage1Row {
  const col1 = column1[rowIdx];
  const col2 = column2[rowIdx];
  const col4 = column4[rowIdx];
  const col5 = column5[rowIdx];
  const col3 = column3Rows?.[rowIdx];
  const fallbackId = rowIdx + 1;
  return {
    id: col1?.id ?? col2?.id ?? col4?.id ?? col5?.id ?? col3?.id ?? fallbackId,
    column1: col1?.word ?? '',
    column2: col2?.word ?? '',
    column3: col3?.options,
    column3SvgMeta: col3?.svgMeta,
    column4: col4?.word ?? '',
    column5: col5?.word ?? '',
  };
}

function buildRows(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
) {
  const rowCount = Math.max(column1.length, column2.length, column4.length, column5.length, column3Rows?.length ?? 0);
  return Array.from({ length: rowCount }, (_, idx) => buildRow(idx, column1, column2, column4, column5, column3Rows));
}

function extractSolution(entry?: LpsColumnEntry): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.word.length) return [];
  return [entry.correctIndex];
}

function extractColumn3Solution(entry?: LpsColumn3Row): number[] {
  if (!entry || typeof entry.correctIndex !== 'number') return [];
  if (entry.correctIndex < 0 || entry.correctIndex >= entry.options.length) return [];
  return [entry.correctIndex];
}

function buildSolutions(
  column1: LpsColumnEntry[],
  column2: LpsColumnEntry[],
  column4: LpsColumnEntry[],
  column5: LpsColumnEntry[],
  column3Rows?: LpsColumn3Row[],
) {
  return buildRows(column1, column2, column4, column5, column3Rows).map((_, idx) => ({
    col1: extractSolution(column1[idx]),
    col2: extractSolution(column2[idx]),
    col3: extractColumn3Solution(column3Rows?.[idx]),
    col4: extractSolution(column4[idx]),
    col5: extractSolution(column5[idx]),
  }));
}

export const LPS_PAGE1_ROWS: LpsPage1Row[] = buildRows(LPS_PAGE1_COLUMN1, LPS_PAGE1_COLUMN2, LPS_PAGE1_COLUMN4, LPS_PAGE1_COLUMN5);

export const LPS_PAGE1_SOLUTIONS: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1,
  LPS_PAGE1_COLUMN2,
  LPS_PAGE1_COLUMN4,
  LPS_PAGE1_COLUMN5,
);

const LPS_PAGE1_COLUMN1_A = LPS_PAGE1_COLUMN1.slice(0, 20);
const LPS_PAGE1_COLUMN2_A = LPS_PAGE1_COLUMN2.slice(0, 20);
const LPS_PAGE1_COLUMN4_A = LPS_PAGE1_COLUMN4.slice(0, 20);
const LPS_PAGE1_COLUMN5_A = LPS_PAGE1_COLUMN4.slice(0, 20);

export const LPS_PAGE1_ROWS_A: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
  LPS_PAGE1_COLUMN5_A,
);

export const LPS_PAGE1_SOLUTIONS_A: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_A,
  LPS_PAGE1_COLUMN2_A,
  LPS_PAGE1_COLUMN4_A,
  LPS_PAGE1_COLUMN5_A,
);

const LPS_PAGE1_COLUMN1_B: LpsColumnEntry[] = [
  { id: 1, word: 'Auyzug', correctIndex: 2 },
  { id: 2, word: 'Freute', correctIndex: 4 },
  { id: 3, word: 'Alaske', correctIndex: 5 },
  { id: 4, word: 'Bezirg', correctIndex: 5 },
  { id: 5, word: 'Ardist', correctIndex: 2 },
  { id: 6, word: 'Waknis', correctIndex: 2 },
  { id: 7, word: 'Gewaih', correctIndex: 3 },
  { id: 8, word: 'Magmet', correctIndex: 3 },
  { id: 9, word: 'Legiom', correctIndex: 5 },
  { id: 10, word: 'Jasmim', correctIndex: 5 },
  { id: 11, word: 'Tiskus', correctIndex: 0 },
  { id: 12, word: 'Panema', correctIndex: 3 },
  { id: 13, word: 'Heliun', correctIndex: 5 },
  { id: 14, word: 'Ratium', correctIndex: 2 },
  { id: 15, word: 'Vugger', correctIndex: 0 },
  { id: 16, word: 'Etison', correctIndex: 1 },
  { id: 17, word: 'Vright', correctIndex: 0 },
  { id: 18, word: 'Prokat', correctIndex: 0 },
  { id: 19, word: 'Attilo', correctIndex: 5 },
  { id: 20, word: 'Dnjebr', correctIndex: 4 },
  { id: 21, word: 'Toulom', correctIndex: 5 },
  { id: 22, word: 'Amatom', correctIndex: 1 },
  { id: 23, word: 'Namtes', correctIndex: 2 },
  { id: 24, word: 'Naffia', correctIndex: 0 },
  { id: 25, word: 'Garies', correctIndex: 0 },
  { id: 26, word: 'Virkil', correctIndex: 3 },
  { id: 27, word: 'Grachd', correctIndex: 5 },
  { id: 28, word: 'Armika', correctIndex: 2 },
  { id: 29, word: 'Mohavk', correctIndex: 4 },
  { id: 30, word: 'Hamgar', correctIndex: 2 },
  { id: 31, word: 'Prodon', correctIndex: 3 },
  { id: 32, word: 'Emplem', correctIndex: 2 },
  { id: 33, word: 'Anemie', correctIndex: 2 },
  { id: 34, word: 'Osires', correctIndex: 4 },
  { id: 35, word: 'Sharge', correctIndex: 0 },
  { id: 36, word: 'Druite', correctIndex: 4 },
  { id: 37, word: 'Behain', correctIndex: 5 },
  { id: 38, word: 'Kaulin', correctIndex: 2 },
  { id: 39, word: 'Läsiom', correctIndex: 5 },
  { id: 40, word: 'Gounot', correctIndex: 5 }
];

const LPS_PAGE1_COLUMN2_B: LpsColumnEntry[] = [
  { id: 1, word: 'Heimad', correctIndex: 5 },
  { id: 2, word: 'Herink', correctIndex: 5 },
  { id: 3, word: 'Expord', correctIndex: 5 },
  { id: 4, word: 'Pulwer', correctIndex: 3 },
  { id: 5, word: 'Jeguar', correctIndex: 1 },
  { id: 6, word: 'Penzin', correctIndex: 0 },
  { id: 7, word: 'Gasino', correctIndex: 0 },
  { id: 8, word: 'Disdel', correctIndex: 3 },
  { id: 9, word: 'Madred', correctIndex: 4 },
  { id: 10, word: 'Rikrut', correctIndex: 1 },
  { id: 11, word: 'Kegend', correctIndex: 0 },
  { id: 12, word: 'Barole', correctIndex: 0 },
  { id: 13, word: 'Rokoke', correctIndex: 5 },
  { id: 14, word: 'Ottawe', correctIndex: 5 },
  { id: 15, word: 'Spagad', correctIndex: 5 },
  { id: 16, word: 'Dewise', correctIndex: 2 },
  { id: 17, word: 'Riflex', correctIndex: 1 },
  { id: 18, word: 'Bombai', correctIndex: 5 },
  { id: 19, word: 'Tegris', correctIndex: 1 },
  { id: 20, word: 'Nimphe', correctIndex: 1 },
  { id: 21, word: 'Delfhi', correctIndex: 3 },
  { id: 22, word: 'Beuxit', correctIndex: 1 },
  { id: 23, word: 'Okolar', correctIndex: 2 },
  { id: 24, word: 'Gheops', correctIndex: 0 },
  { id: 25, word: 'Kripta', correctIndex: 2 },
  { id: 26, word: 'Netrat', correctIndex: 1 },
  { id: 27, word: 'Smirna', correctIndex: 2 },
  { id: 28, word: 'Akelai', correctIndex: 4 },
  { id: 29, word: 'Budgot', correctIndex: 4 },
  { id: 30, word: 'Amelfi', correctIndex: 2 },
  { id: 31, word: 'Alippo', correctIndex: 2 },
  { id: 32, word: 'Komdur', correctIndex: 3 },
  { id: 33, word: 'Bowist', correctIndex: 2 },
  { id: 34, word: 'Prokob', correctIndex: 5 },
  { id: 35, word: 'Honwed', correctIndex: 3 },
  { id: 36, word: 'Laudom', correctIndex: 5 },
  { id: 37, word: 'Pantur', correctIndex: 3 },
  { id: 38, word: 'Ascudo', correctIndex: 0 },
  { id: 39, word: 'Hoplid', correctIndex: 5 },
  { id: 40, word: 'Anubes', correctIndex: 4 },
];
const LPS_PAGE1_COLUMN4_B: LpsColumnEntry[] = [
  { id: 1, word: 'xxxxxxxox', correctIndex: 7 },
  { id: 2, word: 'ababababb', correctIndex: 8 },
  { id: 3, word: 'xoxoxoxoo', correctIndex: 8 },
  { id: 4, word: '123123423', correctIndex: 6 },
  { id: 5, word: '00n00n000', correctIndex: 8 },
  { id: 6, word: 'abcdefchi', correctIndex: 6 },
  { id: 7, word: 'aBaBaBaba', correctIndex: 7 },
  { id: 8, word: '2468912141618', correctIndex: 4 },
  { id: 9, word: 'abcabcdbc', correctIndex: 6 },
  { id: 10, word: '121222121', correctIndex: 4 },
  { id: 11, word: '5105205306405', correctIndex: 6 },
  { id: 12, word: '19171513118753', correctIndex: 5 },
  { id: 13, word: 'abcdeFghi', correctIndex: 5 },
  { id: 14, word: 'jklmnopqs', correctIndex: 8 },
  { id: 15, word: 'BBaBaBaBa', correctIndex: 0 },
  { id: 16, word: 'rqtuvwxyz', correctIndex: 1 },
  { id: 17, word: '349121518212427', correctIndex: 1 },
  { id: 18, word: 'xxoxxoxoo', correctIndex: 7 },
  { id: 19, word: 'ijkLmnopq', correctIndex: 3 },
  { id: 20, word: 'bbcddefghhijj', correctIndex: 4 },
  { id: 21, word: 'ihgfeDcba', correctIndex: 5 },
  { id: 22, word: 'abbcddeeffghhi', correctIndex: 4 },
  { id: 23, word: 'dcedcbdcb', correctIndex: 2 },
  { id: 24, word: '10128146163182', correctIndex: 6 },
  { id: 25, word: 'bCdEFGhIj', correctIndex: 4 },
  { id: 26, word: '151812219245273', correctIndex: 6 },
  { id: 27, word: '234567432', correctIndex: 5 },
  { id: 28, word: '124787421', correctIndex: 4 },
  { id: 29, word: '1fg2dc3ba', correctIndex: 2 },
  { id: 30, word: '125344352', correctIndex: 0 },
  { id: 31, word: 'abegikmoq', correctIndex: 1 },
  { id: 32, word: 'aaceefiikmmoqq', correctIndex: 3 },
  { id: 33, word: '5a4c3d2e1', correctIndex: 1 },
  { id: 34, word: 'rPmLjHfDb', correctIndex: 2 },
  { id: 35, word: '1124394166', correctIndex: 8 },
  { id: 36, word: 'abccadaea', correctIndex: 2 },
  { id: 37, word: 'baedhgkln', correctIndex: 7 },
  { id: 38, word: 'cbfeihjko', correctIndex: 6 },
  { id: 39, word: '1123711162229', correctIndex: 3 },
  { id: 40, word: 'aAbCdEgGh', correctIndex: 8 },
];

const LPS_PAGE1_COLUMN5_B: LpsColumnEntry[] = [
  { id: 1, word: 'LECKF', correctIndex: 4 },
  { id: 2, word: 'AGENW', correctIndex: 4 },
  { id: 3, word: 'TURMS', correctIndex: 4 },
  { id: 4, word: 'TBRAU', correctIndex: 1 },
  { id: 5, word: 'RBAUE', correctIndex: 1 },
  { id: 6, word: 'OCKST', correctIndex: 3 },
  { id: 7, word: 'FUCSH', correctIndex: 0 },
  { id: 8, word: 'BETAU', correctIndex: 2 },
  { id: 9, word: 'AUBST', correctIndex: 3 },
  { id: 10, word: 'EGURK', correctIndex: 1 },
  { id: 11, word: 'DENFA', correctIndex: 3 },
  { id: 12, word: 'RUSTB', correctIndex: 4 },
  { id: 13, word: 'NDFEI', correctIndex: 2 },
  { id: 14, word: 'AWODK', correctIndex: 1 },
  { id: 15, word: 'ELAUN', correctIndex: 1 },
  { id: 16, word: 'TIGRE', correctIndex: 0 },
  { id: 17, word: 'TOURS', correctIndex: 1 },
  { id: 18, word: 'MUSKI', correctIndex: 0 },
  { id: 19, word: 'ENLAK', correctIndex: 2 },
  { id: 20, word: 'NROMA', correctIndex: 1 },
  { id: 21, word: 'UMDAT', correctIndex: 2 },
  { id: 22, word: 'MAFIR', correctIndex: 2 },
  { id: 23, word: 'LHAGE', correctIndex: 1 },
  { id: 24, word: 'DRATH', correctIndex: 0 },
  { id: 25, word: 'ISTEK', correctIndex: 4 },
  { id: 26, word: 'CHEWA', correctIndex: 3 },
  { id: 27, word: 'MEBLU', correctIndex: 2 },
  { id: 28, word: 'RACKW', correctIndex: 4 },
  { id: 29, word: 'NESAH', correctIndex: 2 },
  { id: 30, word: 'ARBEF', correctIndex: 4 },
  { id: 31, word: 'TANGS', correctIndex: 1 },
  { id: 32, word: 'BUCEH', correctIndex: 0 },
  { id: 33, word: 'ARKEM', correctIndex: 4 },
  { id: 34, word: 'AMINK', correctIndex: 4 },
  { id: 35, word: 'BNETO', correctIndex: 0 },
  { id: 36, word: 'OMCHR', correctIndex: 2 },
  { id: 37, word: 'RKEBI', correctIndex: 3 },
  { id: 38, word: 'INRHE', correctIndex: 2 },
  { id: 39, word: 'ACHIN', correctIndex: 1 },
  { id: 40, word: 'IHGON', correctIndex: 1 },
];
export const LPS_PAGE1_ROWS_B: LpsPage1Row[] = buildRows(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
  LPS_PAGE1_COLUMN5_B,
  LPS_PAGE1_COLUMN3_B,
);

export const LPS_PAGE1_SOLUTIONS_B: LpsPage1Solution[] = buildSolutions(
  LPS_PAGE1_COLUMN1_B,
  LPS_PAGE1_COLUMN2_B,
  LPS_PAGE1_COLUMN4_B,
  LPS_PAGE1_COLUMN5_B,
  LPS_PAGE1_COLUMN3_B,
);

export function getLpsDataset(testName?: string): LpsDataset {
  if (testName === 'LPS-A') {
    return { rows: LPS_PAGE1_ROWS_A, solutions: LPS_PAGE1_SOLUTIONS_A };
  }
  if (testName === 'LPS-B') {
    return { rows: LPS_PAGE1_ROWS_B, solutions: LPS_PAGE1_SOLUTIONS_B };
  }
  return { rows: LPS_PAGE1_ROWS, solutions: LPS_PAGE1_SOLUTIONS };
}



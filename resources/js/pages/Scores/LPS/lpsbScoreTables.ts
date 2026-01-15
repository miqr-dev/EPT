CtrlV
VS Code Extension
Code sharing at the speed of paste
Shared Snippet
JavaScript
Created about 3 hours ago
Expires in 2 days
4 views
Scan: clean

VS Code
New
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66
67
⌄
⌄
⌄
⌄
⌄
⌄
⌄
⌄
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



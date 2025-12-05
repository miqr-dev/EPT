/**
 * @fileoverview Composable for MRT-B test logic and scoring.
 */

// --- Static Data ---
/**
 * Array of questions for the MRT-B test.
 * @type {Array<Object>}
 */
const mrtBQuestions = [
    { number: 1, options: ['Dezimahlwaage', 'Dezimahlwage', 'Dezimalwaage', 'Dezimalwage'], correct: ['C'] },
    { number: 2, options: ['trübselig', 'trühbseelig', 'trübseelig', 'trübsehlig'], correct: ['A'] },
    { number: 3, options: ['Haarschehre', 'Hahrschere', 'Haarscheere', 'Haarschere'], correct: ['D'] },
    { number: 4, options: ['Urahnen', 'Uhranen', 'Uhrahnen', 'Uranen'], correct: ['A'] },
    { number: 5, options: ['Wiederwal', 'Wiederwahl', 'Wiederwaal', 'Widerwahl'], correct: ['B'] },
    { number: 6, options: ['sich erinern', 'sich errinnern', 'sich errinern', 'sich erinnern'], correct: ['D'] },
    { number: 7, options: ['Gemäldegalerie', 'Gemäldegallerie', 'Gemäldegallerrie', 'Gemäldegalerrie'], correct: ['A'] },
    { number: 8, options: ['kapputt', 'kapput', 'kaput', 'kaputt'], correct: ['D'] },
    { number: 9, options: ['Tellegrammbote', 'Tellegrambote', 'Telegrammbote', 'Telegrambote'], correct: ['C'] },
    { number: 10, options: ['Parallele', 'Parallelle', 'Paralelle', 'Paralele'], correct: ['A'] },
    { number: 11, options: ['fortwährent', 'fordwährend', 'fordwährent', 'fortwährend'], correct: ['D'] },
    { number: 12, options: ['beret und beretsam', 'beredt und beredsam', 'bered und beredsam', 'beredt und beretsam'], correct: ['B'] },
    { number: 13, options: ['die Grimmschen Märchen', 'die Grimmschen Märschen', 'die Grimmchen Märschen', 'die Grimmchen Märchen'], correct: ['A'] },
    { number: 14, options: ['Gemüdlichkeid', 'Gemütlichkeit', 'Gemütlichkeid', 'Gemüdlichkeit'], correct: ['B'] },
    { number: 15, options: ['Gesandtschaft', 'Gesandschaft', 'Gesandtschafd', 'Gesantschaft'], correct: ['A'] },
    { number: 16, options: ['Quartirmiete', 'Quartirmite', 'Quartiermite', 'Quartiermiete'], correct: ['D'] },
    { number: 17, options: ['Fabriekschlot', 'Fabrikschlot', 'Fabrikschloht', 'Fabrikschloot'], correct: ['B'] },
    { number: 18, options: ['Zuhörertribühne', 'Zuhörertriebüne', 'Zuhörertribüne', 'Zuhörertriebühne'], correct: ['C'] },
    { number: 19, options: ['mimosenhaft', 'miemoosenhaft', 'miemosenhaft', 'mimoosenhaft'], correct: ['A'] },
    { number: 20, options: ['Augenliderhärchen', 'Augenliederhäärchen', 'Augenliederhärchen', 'Augenliderhäärchen'], correct: ['A'] },
    { number: 21, options: ['Galloprennbahn', 'Gallopprennbahn', 'Galopprennbahn', 'Galoprennbahn'], correct: ['C'] },
    { number: 22, options: ['interressant', 'interessant', 'interesant', 'interresant'], correct: ['B'] },
    { number: 23, options: ['kulturrell', 'kullturrell', 'kulturrel', 'kulturell'], correct: ['D'] },
    { number: 24, options: ['Balletttruppe', 'Ballettruppe', 'Balettruppe', 'Baletttruppe'], correct: ['A'] },
    { number: 25, options: ['Karoserie', 'Karrosserie', 'Karosserrie', 'Karosserie'], correct: ['D'] },
    {
        number: 26,
        options: ['wohlwissent und wissendlich', 'wohlwissend und wissendlich', 'wohlwissend und wissentlich', 'wohlwissent und wissentlich'],
        correct: ['C'],
    },
    { number: 27, options: ['Gelenkichkeit', 'Gelengichkeit', 'Gelenkigkeit', 'Gelengigkeit'], correct: ['C'] },
    { number: 28, options: ['endgüldig', 'entgültig', 'entgüldig', 'endgültig'], correct: ['D'] },
    { number: 29, options: ['Quatrat', 'Quadrad', 'Quadrat', 'Quatrad'], correct: ['C'] },
    { number: 30, options: ['Abgeortneter', 'Abgeordneder', 'Abgeortneder', 'Abgeordneter'], correct: ['D'] },
    {
        number: 31,
        options: ['ein Brotleib als Leibspeise', 'ein Brotlaib als Laibspeise', 'ein Brotlaib als Leibspeise', 'ein Brotleib als Laibspeise'],
        correct: ['C'],
    },
    { number: 32, options: ['Labyrinth', 'Labirynth', 'Labirinth', 'Labyrynth'], correct: ['A'] },
    { number: 33, options: ['Heidekreuter', 'Haidekreuter', 'Haidekräuter', 'Heidekräuter'], correct: ['D'] },
    { number: 34, options: ['Zilinder', 'Zylynder', 'Zylinder', 'Zilynder'], correct: ['C'] },
    { number: 35, options: ['Presseärklärung', 'Presseerklärung', 'Presseerklerung', 'Presseärklerung'], correct: ['B'] },
    {
        number: 36,
        options: [
            'am Sonntag und Feiertags geschlossen',
            'am sonntag und feiertags geschlossen',
            'am sonntag und Feiertags geschlossen',
            'am Sonntag und feiertags geschlossen',
        ],
        correct: ['D'],
    },
    {
        number: 37,
        options: ['heut zu tage und hierzulande', 'heutzutage und Hierzulande', 'heutzutage und hierzulande', 'Heutzutage und hier zu Lande'],
        correct: ['C', 'D'],
    },
    {
        number: 38,
        options: ['es tut uns von Herzen Leid', 'es tut uns von Herzen leid', 'es tut uns von herzen Leid', 'es tut uns von herzen leid'],
        correct: ['B'],
    },
    {
        number: 39,
        options: [
            'er ließ sich nichts zu Schulden kommen',
            'er ließ sich Nichts zuschulden kommen',
            'er ließ sich nichts zuschulden kommen',
            'er ließ sich nichts Zuschulden kommen',
        ],
        correct: ['A', 'C'],
    },
    {
        number: 40,
        options: ['es ist Alles Null und Nichtig', 'es ist alles Null und Nichtig', 'es ist alles null und nichtig', 'es ist alles Null und nichtig'],
        correct: ['C'],
    },
    { number: 41, options: ['opositionell', 'oppositionell', 'oppositionäll', 'oppossitionell'], correct: ['B'] },
    { number: 42, options: ['Portemonnaie', 'Portemonneie', 'Portmonneie', 'Portmonnaie'], correct: ['A'] },
    { number: 43, options: ['Anegdote', 'Anegtode', 'Anekdote', 'Anektode'], correct: ['C'] },
    { number: 44, options: ['konkurrenzfähig', 'konkurrentzfähig', 'konnkurrenzfähig', 'konkurenzfähig'], correct: ['A'] },
    { number: 45, options: ['Dissziplin', 'Disziblin', 'Disziplin', 'Disziplien'], correct: ['C'] },
    { number: 46, options: ['Laienvorställung', 'Leienvorställung', 'Laienvorstellung', 'Leienvorstellung'], correct: ['C'] },
    { number: 47, options: ['Ährenkerner', 'Ehrenkörner', 'Ehrenkerner', 'Ährenkörner'], correct: ['D'] },
    {
        number: 48,
        options: ['schwerfällig und dickfällig', 'schwerfällig und dickfellig', 'schwerfellig und dickfellig', 'schwerfellig und dickfällig'],
        correct: ['B'],
    },
    {
        number: 49,
        options: ['zwei Kähne sind gekäntert', 'zwei Kehne sind gekentert', 'zwei Kähne sind gekentert', 'zwei Kehne sind gekäntert'],
        correct: ['C'],
    },
    { number: 50, options: ['Scheunengemeuer', 'Schäunengemäuer', 'Schäunengemeuer', 'Scheunengemäuer'], correct: ['D'] },
    {
        number: 51,
        options: [
            'sie hat mich nur die Paar Male besucht',
            'sie hat mich nur die paar male besucht',
            'sie hat mich nur die paar Male besucht',
            'sie hat mich nur die Paar male besucht',
        ],
        correct: ['C'],
    },
    { number: 52, options: ['ihm ist Alles Recht', 'ihm ist Alles recht', 'ihm ist alles Recht', 'ihm ist alles recht'], correct: ['D'] },
    {
        number: 53,
        options: ['sie kam schlag Acht Uhr', 'sie kam schlag acht Uhr', 'sie kam Schlag acht Uhr', 'sie kam Schlag Acht Uhr'],
        correct: ['C'],
    },
    { number: 54, options: ['ein bis zweimal', 'ein bis zwei Mal', 'ein- bis zweimal', 'ein- bis zwei mal'], correct: ['C'] },
    {
        number: 55,
        options: [
            'es gibt schweizer Käse und Bayrisches Bier',
            'es gibt Schweizer Käse und bayrisches Bier',
            'es gibt Schweizer Käse und Bayrisches Bier',
            'es gibt schweizer Käse und bayrisches Bier',
        ],
        correct: ['B'],
    },
    { number: 56, options: ['faszinieren', 'faßzinieren', 'faszinnieren', 'fastzinieren'], correct: ['A'] },
    { number: 57, options: ['Subwention', 'Supvention', 'Subvention', 'Suppvention'], correct: ['C'] },
    { number: 58, options: ['Indiskrätion', 'Indiskrezion', 'Indisgretion', 'Indiskretion'], correct: ['D'] },
    { number: 59, options: ['Krutzifix', 'Kruzifichs', 'Kruzifix', 'Gruzifix'], correct: ['C'] },
    { number: 60, options: ['Patzifist', 'Pazifisd', 'Pazivist', 'Pazifist'], correct: ['D'] },
];

/**
 * Maps question indices to their respective groups.
 * @type {Array<Array<number>>}
 */
const groupMap = [
    [0, 1, 2, 3, 4, 15, 16, 17, 18, 19], // U1
    [5, 6, 7, 8, 9, 20, 21, 22, 23, 24], // U2
    [10, 11, 12, 13, 14, 25, 26, 27, 28, 29], // U3
    [30, 31, 32, 33, 34, 45, 46, 47, 48, 49], // U4
    [35, 36, 37, 38, 39, 50, 51, 52, 53, 54], // U5
    [40, 41, 42, 43, 44, 55, 56, 57, 58, 59], // U6
];

/**
 * Stanine values matrix for age group 18-30.
 * @type {Array<Array<number>>}
 */
const SN_WERTE_MATRIX_18_30 = [
    // 0  1  2  3  4  5  6  7  8  9  10
    [1, 1, 1, 1, 2, 2, 3, 4, 5, 7, 9], // U1
    [1, 1, 1, 2, 3, 4, 4, 6, 7, 8, 9], // U2
    [1, 1, 1, 1, 2, 3, 3, 4, 5, 7, 9], // U3
    [1, 1, 1, 1, 1, 1, 2, 3, 4, 6, 9], // U4
    [1, 1, 2, 3, 4, 5, 5, 6, 7, 8, 9], // U5
    [1, 1, 1, 1, 2, 3, 4, 5, 5, 7, 9], // U6
];

/**
 * Stanine values matrix for age group 31-50.
 * @type {Array<Array<number>>}
 */
const SN_WERTE_MATRIX_31_50 = [
    // 0  1  2  3  4  5  6  7  8  9  10
    [1, 1, 1, 1, 2, 3, 4, 4, 6, 7, 9], // U1
    [1, 1, 2, 2, 3, 4, 4, 6, 7, 8, 9], // U2
    [1, 1, 1, 2, 2, 3, 4, 4, 5, 7, 9], // U3
    [1, 1, 1, 1, 2, 2, 3, 3, 5, 6, 9], // U4
    [1, 2, 3, 4, 4, 5, 6, 7, 7, 8, 9], // U5
    [1, 1, 1, 2, 3, 3, 4, 5, 6, 7, 9], // U6
];

/**
 * Percentile rank table for age group 18-30.
 * @type {Array<Object>}
 */
const prTable_18_30=[{rwgs:9,PR:0},{rwgs:10,PR:0},{rwgs:11,PR:0},{rwgs:12,PR:0},{rwgs:13,PR:1},{rwgs:14,PR:1},{rwgs:15,PR:1},{rwgs:16,PR:1},{rwgs:17,PR:1},{rwgs:18,PR:1},{rwgs:19,PR:1},{rwgs:20,PR:2},{rwgs:21,PR:2},{rwgs:22,PR:4},{rwgs:23,PR:4},{rwgs:24,PR:5},{rwgs:25,PR:7},{rwgs:26,PR:8},{rwgs:27,PR:10},{rwgs:28,PR:12},{rwgs:29,PR:13},{rwgs:30,PR:16},{rwgs:31,PR:18},{rwgs:32,PR:21},{rwgs:33,PR:24},{rwgs:34,PR:27},{rwgs:35,PR:31},{rwgs:36,PR:31},{rwgs:37,PR:34},{rwgs:38,PR:38},{rwgs:39,PR:42},{rwgs:40,PR:46},{rwgs:41,PR:50},{rwgs:42,PR:54},{rwgs:43,PR:58},{rwgs:44,PR:66},{rwgs:45,PR:69},{rwgs:46,PR:73},{rwgs:47,PR:76},{rwgs:48,PR:79},{rwgs:49,PR:82},{rwgs:50,PR:86},{rwgs:51,PR:88},{rwgs:52,PR:90},{rwgs:53,PR:93},{rwgs:54,PR:96},{rwgs:55,PR:97},{rwgs:56,PR:99},{rwgs:57,PR:100},{rwgs:58,PR:100},{rwgs:59,PR:100},{rwgs:60,PR:100}];
/**
 * Percentile rank table for age group 31-50.
 * @type {Array<Object>}
 */
const prTable_31_50=[{rwgs:9,PR:0},{rwgs:10,PR:0},{rwgs:11,PR:0},{rwgs:12,PR:0},{rwgs:13,PR:0},{rwgs:14,PR:0},{rwgs:15,PR:1},{rwgs:16,PR:1},{rwgs:17,PR:1},{rwgs:18,PR:2},{rwgs:19,PR:2},{rwgs:20,PR:3},{rwgs:21,PR:3},{rwgs:22,PR:4},{rwgs:23,PR:5},{rwgs:24,PR:5},{rwgs:25,PR:7},{rwgs:26,PR:7},{rwgs:27,PR:8},{rwgs:28,PR:10},{rwgs:29,PR:12},{rwgs:30,PR:13},{rwgs:31,PR:13},{rwgs:32,PR:16},{rwgs:33,PR:18},{rwgs:34,PR:18},{rwgs:35,PR:21},{rwgs:36,PR:21},{rwgs:37,PR:24},{rwgs:38,PR:27},{rwgs:39,PR:31},{rwgs:40,PR:34},{rwgs:41,PR:38},{rwgs:42,PR:42},{rwgs:43,PR:46},{rwgs:44,PR:50},{rwgs:45,PR:54},{rwgs:46,PR:58},{rwgs:47,PR:62},{rwgs:48,PR:66},{rwgs:49,PR:69},{rwgs:50,PR:73},{rwgs:51,PR:76},{rwgs:52,PR:79},{rwgs:53,PR:84},{rwgs:54,PR:90},{rwgs:55,PR:92},{rwgs:56,PR:95},{rwgs:57,PR:96},{rwgs:58,PR:98},{rwgs:59,PR:100},{rwgs:60,PR:100}];

/**
 * A composable function that provides MRT-B test data and scoring logic.
 *
 * @returns {{
 *   mrtQuestions: Array<Object>,
 *   calculateScores: (answers: { user_answer: string | null }[], userAge: number | null) => Object
 * }}
 */
export function useMrtB() {
    /**
     * Checks if a user's answer is correct.
     * @param {string | null} userAnswer - The user's answer.
     * @param {string[]} validAnswers - An array of correct answers.
     * @returns {boolean} - True if the answer is correct, otherwise false.
     */
    function isCorrectAnswer(userAnswer: string | null, validAnswers: string[]): boolean {
        if (!userAnswer) return false;
        return validAnswers.map((a) => a.toUpperCase()).includes(userAnswer.toUpperCase());
    }

    /**
     * Calculates the scores for the MRT-B test.
     * @param {{ user_answer: string | null }[]} answers - An array of user answers.
     * @param {number | null} userAge - The age of the user.
     * @returns {Object} - The calculated scores.
     */
    const calculateScores = (answers: { user_answer: string | null }[], userAge: number | null) => {
        const selectedMatrix = userAge && userAge >= 31 ? SN_WERTE_MATRIX_31_50 : SN_WERTE_MATRIX_18_30;
        const selectedPRTable = userAge && userAge >= 31 ? prTable_31_50 : prTable_18_30;

        const groupScores = groupMap.map((indices) =>
            indices.reduce((sum, idx) => {
                const ans = answers[idx]?.user_answer;
                if (!ans) return sum;
                const q = mrtBQuestions[idx];
                return sum + (isCorrectAnswer(ans, q.correct) ? 1 : 0);
            }, 0),
        );

        const totalScore = groupScores.reduce((a, b) => a + b, 0);

        const groupStanines = groupScores.map((rw, groupIdx) => {
            const safeRW = Math.max(0, Math.min(10, rw));
            return selectedMatrix[groupIdx][safeRW];
        });

        const prValue = selectedPRTable.find((row) => row.rwgs === totalScore)?.PR ?? 0;

        const detailedAnswers = mrtBQuestions.map((q, i) => ({
            number: q.number,
            user_answer: answers[i]?.user_answer ?? null,
            correct_answers: q.correct,
            is_correct: isCorrectAnswer(answers[i]?.user_answer, q.correct),
        }));

        return {
            group_scores: groupScores,
            group_stanines: groupStanines,
            total_score: totalScore,
            prozentrang: prValue,
            answers: detailedAnswers,
        };
    };

    return {
        mrtQuestions: mrtBQuestions,
        calculateScores,
    };
}

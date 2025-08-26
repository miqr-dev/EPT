// resources/js/pages/Questions/LMTQuestions.ts

export interface LMTQuestion {
  number: number;
  text: string;
  options: {
    category: string | number;
    points: number;
    group?: 'L1' | 'L2' | 'F+' | 'F-';
  }[];
}

export const LMT_QUESTIONS: LMTQuestion[] = [
  // ----------------------------------------
  // PAGE 1
  // ----------------------------------------
  {
    number: 1,
    text: "Wenn ich mich mit meiner Zukunft beschäftige, denke ich meistens",
    options: [
      { category: "sehr weit voraus", points: 1, group: "L1" },
      { category: "weit voraus", points: 1, group: "L1" },
      { category: "ziemlich weit voraus", points: 0 },
      { category: "nicht so weit voraus", points: 0 },
    ],
  },
  {
    number: 2,
    text: "Ich denke, daß Leute, die in meinem Alter sind,",
    options: [
      { category: "zu hart arbeiten", points: 0 },
      { category: "hart genug arbeiten", points: 0 },
      { category: "härter arbeiten sollten", points: 1, group: "L2" },
    ],
  },
  {
    number: 3,
    text: "Wenn ich mir vorstelle, in einer Bewerbungssituation zu stehen, dann erlebe ich mich als",
    options: [
      { category: "ruhig", points: 0 },
      { category: "leicht angespannt", points: 0 },
      { category: "ziemlich nervös", points: 1, group: "F-" },
    ],
  },
  {
    number: 4,
    text: "Wenn ich in schwierigen Situationen etwas erregt bin, fühle ich mich",
    options: [
      { category: "weniger stark als sonst", points: 0 },
      { category: "ebenso stark wie sonst", points: 1, group: "F+" },
      { category: "stärker als sonst", points: 1, group: "F+" },
    ],
  },
  {
    number: 5,
    text: "Beim Arbeiten sind die Anforderungen, die ich an mich selber stelle,",
    options: [
      { category: "sehr hoch", points: 1, group: "L1" },
      { category: "hoch", points: 1, group: "L1" },
      { category: "ziemlich hoch", points: 0 },
      { category: "nicht so hoch", points: 0 },
    ],
  },
  {
    number: 6,
    text: "Wenn ich im Voraus weiß, daß ich mich einem Intelligenztest unterziehen muß, dann fühle ich mich",
    options: [
      { category: "völlig gelassen", points: 0 },
      { category: "gelassen", points: 0 },
      { category: "nicht so gelassen", points: 1, group: "F-" },
      { category: "sehr angespannt", points: 1, group: "F-" },
    ],
  },
  {
    number: 7,
    text: "Das Sprichwort „Zeit ist Geld“ finde ich",
    options: [
      { category: "richtig", points: 1, group: "L1" },
      { category: "teilweise richtig", points: 1, group: "L1" },
      { category: "etwas übertrieben", points: 0 },
    ],
  },
  {
    number: 8,
    text: "Wenn ich in Prüfungen bin, habe ich",
    options: [
      { category: "oft feuchte Hände", points: 1, group: "F-" },
      { category: "manchmal feuchte Hände", points: 1, group: "F-" },
      { category: "selten feuchte Hände", points: 0 },
    ],
  },
  {
    number: 9,
    text: "Wenn ich etwas beginne, bringe ich es",
    options: [
      { category: "nie zu einem guten Abschluß", points: 0 },
      { category: "selten zu einem guten Abschluß", points: 0 },
      { category: "manchmal zu einem guten Abschluß", points: 0 },
      { category: "meistens zu einem guten Abschluß", points: 1, group: "L2" },
    ],
  },
  {
    number: 10,
    text: "Ich denke, daß, wenn ich während einer Prüfung etwas Angst habe, mein Gedächtnis",
    options: [
      { category: "besser ist als sonst", points: 1, group: "F+" },
      { category: "ebenso gut ist wie sonst", points: 1, group: "F+" },
      { category: "weniger gut ist als sonst", points: 0 },
    ],
  },
  {
    number: 11,
    text: "Wenn ich mich frage, ob ich in schwierigen Situationen Angst habe, mich zu blamieren, muß ich sagen, daß dieser Gedanke mich",
    options: [
      { category: "manchmal bedrückt", points: 1, group: "F-" },
      { category: "nicht so sehr bedrückt", points: 0 },
    ],
  },
  {
    number: 12,
    text: "Mehr zu leisten als andere, halte ich für",
    options: [
      { category: "sehr wichtig", points: 1, group: "L1" },
      { category: "wichtig", points: 1, group: "L1" },
      { category: "ziemlich wichtig", points: 1, group: "L1" },
      { category: "nicht so wichtig", points: 0 },
    ],
  },
  {
    number: 13,
    text: "Wenn ich kurz vor einer Prüfung stehe, dann bin ich",
    options: [
      { category: "ziemlich nervös", points: 1, group: "F-" },
      { category: "manchmal etwas nervös", points: 1, group: "F-" },
      { category: "etwas mehr angespannt als sonst", points: 0 },
    ],
  },
  {
    number: 14,
    text: "Ein Leben, in dem ich nicht zu arbeiten bräuchte, würde ich",
    options: [
      { category: "angenehm finden", points: 0 },
      { category: "nicht angenehm finden", points: 0 },
      { category: "unangenehm finden", points: 1, group: "L2" },
    ],
  },
  {
    number: 15,
    text: "Wenn ich leicht erregt bin, kann ich",
    options: [
      { category: "besser lernen als sonst", points: 1, group: "F+" },
      { category: "genau so gut lernen wie sonst", points: 1, group: "F+" },
      { category: "weniger gut lernen als sonst", points: 0 },
    ],
  },

  // ----------------------------------------
  // PAGE 2
  // ----------------------------------------
  {
    number: 16,
    text: "Als ich zur Schule ging, war mir der Wunsch, es im Leben weit zu bringen,",
    options: [
      { category: "nicht so wichtig", points: 0 },
      { category: "ziemlich wichtig", points: 1, group: "L1" },
      { category: "wichtig", points: 1, group: "L1" },
    ],
  },
  {
    number: 17,
    text: "Bei Gesprächen mit Fremden fühle ich mich",
    options: [
      { category: "stets gelassen", points: 0 },
      { category: "nicht immer völlig selbstsicher", points: 1, group: "F-" },
      { category: "meistens nicht gelassen", points: 1, group: "F-" },
    ],
  },
  {
    number: 18,
    text: "Wenn ich eine schwierige Aufgabe erst einmal angefangen habe, fällt mir das Aufhören",
    options: [
      { category: "leicht", points: 0 },
      { category: "nicht immer leicht", points: 0 },
      { category: "schwer", points: 1, group: "L2" },
      { category: "sehr schwer", points: 1, group: "L2" },
    ],
  },
  {
    number: 19,
    text: "Das Bedürfnis, die Ansprüche an mich selbst zu steigern, ist bei mir",
    options: [
      { category: "stark ausgeprägt", points: 1, group: "L1" },
      { category: "ziemlich stark ausgeprägt", points: 1, group: "L1" },
      { category: "nicht so stark ausgeprägt", points: 0 },
    ],
  },
  {
    number: 20,
    text: "Wenn ich vor der Klasse aufgerufen wurde, hatte ich",
    options: [
      { category: "oft Herzklopfen", points: 1, group: "F-" },
      { category: "selten Herzklopfen", points: 0 },
      { category: "nie Herzklopfen", points: 0 },
    ],
  },
  {
    number: 21,
    text: "An einer Sache lange zu arbeiten, ohne zu ermüden,",
    options: [
      { category: "fällt mir schwer", points: 0 },
      { category: "gelingt mir nicht so gut", points: 0 },
      { category: "fällt mir leicht", points: 1, group: "L2" },
    ],
  },
  {
    number: 22,
    text: "Ein bißchen Angst ist für meine Leistungen",
    options: [
      { category: "nie günstig", points: 0 },
      { category: "selten günstig", points: 0 },
      { category: "oft günstig", points: 1, group: "F+" },
    ],
  },
  {
    number: 23,
    text: "Wenn ich vor einer wichtigen Aufgabe stehe, bei der die Wahrscheinlichkeit zu scheitern groß ist, fühle ich mich",
    options: [
      { category: "unruhig", points: 1, group: "F-" },
      { category: "ziemlich unruhig", points: 1, group: "F-" },
      { category: "ziemlich ruhig", points: 0 },
      { category: "ruhig", points: 0 },
    ],
  },
  {
    number: 24,
    text: "Für mich ist Arbeiten etwas, was ich",
    options: [
      { category: "nur manchmal tun würde", points: 0 },
      { category: "gerne tue, was mich aber oft Mühe kostet", points: 0 },
      { category: "immer gerne tue", points: 1, group: "L1" },
    ],
  },
  {
    number: 25,
    text: "Wenn ich mich auf eine Aufgabe nicht so gut vorbereitet hatte und befürchten mußte, in der Schule dranzukommen, fühlte ich mich",
    options: [
      { category: "ziemlich ruhig", points: 0 },
      { category: "ziemlich unruhig", points: 0 },
      { category: "sehr unwohl", points: 1, group: "F-" },
    ],
  },
  {
    number: 26,
    text: "In der Schule war ich",
    options: [
      { category: "sehr ehrgeizig", points: 1, group: "L2" },
      { category: "ziemlich ehrgeizig", points: 1, group: "L2" },
      { category: "wenig ehrgeizig", points: 0 },
    ],
  },
  {
    number: 27,
    text: "In der Schule empfand ich für Menschen, die es im Leben weit gebracht hatten,",
    options: [
      { category: "sehr große Bewunderung", points: 1, group: "L1" },
      { category: "große Bewunderung", points: 1, group: "L1" },
      { category: "ziemlich große Bewunderung", points: 0 },
      { category: "wenig Bewunderung", points: 0 },
    ],
  },
  {
    number: 28,
    text: "Während einer mündlichen oder schriftlichen Prüfung hatte ich den Gedanken, vielleicht schlecht abzuschneiden,",
    options: [
      { category: "selten", points: 0 },
      { category: "ziemlich selten", points: 0 },
      { category: "ziemlich häufig", points: 1, group: "F-" },
      { category: "häufig", points: 1, group: "F-" },
    ],
  },
  {
    number: 29,
    text: "In der Schule hielten mich die Leute für",
    options: [
      { category: "fleißig", points: 1, group: "L2" },
      { category: "nicht immer gleich fleißig", points: 0 },
      { category: "ziemlich bequem", points: 0 },
    ],
  },
  {
    number: 30,
    text: "Wenn ich kurz vor einer Prüfung stehe und mir über ihren Ausgang Sorgen mache, dann kann ich",
    options: [
      { category: "leichter lernen als sonst", points: 1, group: "F+" },
      { category: "genau so gut lernen wie sonst", points: 1, group: "F+" },
      { category: "schlechter lernen als sonst", points: 0 },
    ],
  },
  {
    number: 31,
    text: "In Verwirrung gerate ich in Situationen, in denen es darauf ankommt.",
    options: [
      { category: "selten", points: 0 },
      { category: "oft", points: 1, group: "F-" },
      { category: "sehr oft", points: 1, group: "F-" },
    ],
  },
  // ----------------------------------------
  // PAGE 3
  // ----------------------------------------
  {
    number: 32,
    text: "Was andere über meine Leistungen denken, ist für mich",
    options: [
      { category: "wichtig", points: 1, group: "L1" },
      { category: "ziemlich wichtig", points: 0 },
      { category: "nicht so wichtig", points: 0 },
    ],
  },
  {
    number: 33,
    text: "Wenn ich in der Schule überraschend eine Klassenarbeit schreiben mußte, geriet ich",
    options: [
      { category: "kaum in Panik", points: 0 },
      { category: "in Panik", points: 1, group: "F-" },
      { category: "stark in Panik", points: 1, group: "F-" },
    ],
  },
  {
    number: 34,
    text: "Sich lange auf eine wichtige Aufgabe vorzubereiten,",
    options: [
      { category: "ist häufig voreilig", points: 0 },
      { category: "kann manchmal nützlich sein", points: 0 },
      { category: "zeugt von Realitätssinn", points: 1, group: "L2" },
    ],
  },
  {
    number: 35,
    text: "Wenn mich etwas in Anspannung versetzt, kann ich",
    options: [
      { category: "weniger gut arbeiten als sonst", points: 0 },
      { category: "ebenso gut arbeiten wie sonst", points: 1, group: "F+" },
      { category: "besser arbeiten als sonst", points: 1, group: "F+" },
    ],
  },
  {
    number: 36,
    text: "In der Schule waren im allgemeinen die Ansprüche, die ich in einem Lerngebiet an mich selbst stellte,",
    options: [
      { category: "sehr hoch", points: 1, group: "L1"},
      { category: "hoch", points: 1, group: "L1"},
      { category: "ziemlich hoch", points: 0 },
      { category: "niedrig", points: 0 },
    ],
  },
  {
    number: 37,
    text: "In Situationen, in denen es darauf ankommt, unterlaufen mir Fehler",
    options: [
      { category: "selten", points: 0 },
      { category: "ziemlich oft", points: 1, group: "F-"},
    ],
  },
  {
    number: 38,
    text: "Es im Leben zu etwas zu bringen,",
    options: [
      { category: "wird in seiner Bedeutung überschätzt", points: 0 },
      { category: "nimmt in unserer Gesellschaft einen wichtigen Platz ein", points: 0 },
      { category: "stellt ein erstrebenswertes Ziel dar", points: 1, group: "L1" },
    ],
  },
  {
    number: 39,
    text: "Wenn ich in der Schule bei einer schriftlichen Prüfung merkte, daß ich es zeitlich nicht schaffen würde,",
    options: [
      { category: "geriet ich in Panik", points: 1, group: "F-" },
      { category: "konnte ich mich noch konzentrieren", points: 1, group: "F-" },
      { category: "blieb ich ziemlich ruhig", points: 0 },
    ],
  },
  {
    number: 40,
    text: "Organisieren macht mir",
    options: [
      { category: "Spaß", points: 1, group: "L2" },
      { category: "nicht so viel Spaß", points: 0 },
      { category: "überhaupt keinen Spaß", points: 0 },
    ],
  },
  {
    number: 41,
    text: "Wenn ich ein Ziel nicht erreicht oder eine Aufgabe nicht gut beendet habe,",
    options: [
      { category: "setze ich alles daran, es doch noch zu schaffen", points: 1, group: "L1" },
      { category: "versuche ich es eben auf eine andere Art", points: 0 },
      { category: "fällt es mir schwer, den Mut nicht sinken zu lassen", points: 0 },
    ],
  },
  {
    number: 42,
    text: "Wenn ich mich frage, ob ich nervös bin, dann muß ich sagen, daß ich",
    options: [
      { category: "tatsächlich oft nervös bin", points: 1, group: "F-" },
      { category: "nicht so oft nervös bin", points: 0 },
    ],
  },
  {
    number: 43,
    text: "Wenn ich mit einer schwierigen Sache beschäftigt bin,",
    options: [
      { category: "gebe ich manchmal auf", points: 0 },
      { category: "schiebe ich sie auf und mache später weiter", points: 0 },
      { category: "bleibe ich meistens dabei", points: 1, group: "L2" },
    ],
  },
  {
    number: 44,
    text: "Ich denke, daß etwas Prüfungsangst",
    options: [
      { category: "zu besseren Leistungen führt",points: 1, group: "F+" },
      { category: "die Leistungen kaum beeinflußt",points: 1, group: "F+" },
      { category: "zu schlechteren Leistungen führt", points: 0 },
    ],
  },
  {
    number: 45,
    text: "In der Schule hielt ich eine gute Beziehung zum Lehrer für",
    options: [
      { category: "unwichtig", points: 0 },
      { category: "ziemlich wichtig", points: 0 },
      { category: "wichtig", points: 1, group: "L2" },
    ],
  },
  {
    number: 46,
    text: "Wenn ich mich frage, ob meine Leistungen in einer Prüfung durch meine Erregung beeinflußt worden sind, dann muß ich sagen,",
    options: [
      { category: "daß dies im starken Maße der Fall ist", points: 1, group: "F-" },
      { category: "daß dies in ziemlich starkem Maße der Fall ist", points: 1, group: "F-" },
      { category: "daß dies nicht der Fall ist", points: 0 },
    ],
  },
  {
    number: 47,
    text: "Klassenkameraden, die alles taten, um die besten Schüler zu sein, fand ich",
    options: [
      { category: "sehr unsympathisch", points: 0 },
      { category: "unsympathisch", points: 0 },
      { category: "sympathisch", points: 1, group: "L1" },
      { category: "sehr sympathisch", points: 1, group: "L1" },
    ],
  },
  // ----------------------------------------
  // PAGE 4
  // ----------------------------------------
  {
    number: 48,
    text: "Wenn ich ein bißchen ängstlich und angespannt bin, kann ich",
    options: [
      { category: "weniger gut denken als normalerweise", points: 0 },
      { category: "ebenso gut denken wie normalerweise", points: 1, group: "F+"},
      { category: "besser denken als normalerweise", points: 1, group: "F+"},
    ],
  },
  {
    number: 49,
    text: "Am Wochenende zu lernen fällt mir",
    options: [
      { category: "schwer", points: 0 },
      { category: "leicht", points: 1, group: "L1" },
    ],
  },
  {
    number: 50,
    text: "Wenn ich nervös bin, arbeite ich meistens",
    options: [
      { category: "weniger gut als sonst", points: 0 },
      { category: "ebenso gut wie sonst", points: 1, group: "F+" },
      { category: "besser als sonst", points: 1, group: "F+" },
    ],
  },
  {
    number: 51,
    text: "Wenn unerwartet eine wichtige Sache auf mich zukommt, dann fühle ich mich",
    options: [
      { category: "sehr unsicher", points: 1, group: "F-"},
      { category: "unsicher", points: 1, group: "F-"},
      { category: "ebenso ruhig wie sonst", points: 0 },
    ],
  },
  {
    number: 52,
    text: "Im Allgemeinen bin ich",
    options: [
      { category: "stark auf die Zukunft ausgerichtet", points: 1, group: "L2" },
      { category: "nicht so stark auf die Zukunft ausgerichtet", points: 0 },
      { category: "wenig auf die Zukunft ausgerichtet", points: 0 },
    ],
  },
  {
    number: 53,
    text: "Die Umstellung auf neue Situationen fällt mir",
    options: [
      { category: "sehr leicht", points: 0 },
      { category: "leicht", points: 0 },
      { category: "schwer", points: 1, group: "F-" },
      { category: "sehr schwer", points: 1, group: "F-" },
    ],
  },
  {
    number: 54,
    text: "Wenn ich mir frühere Situationen vorstelle, in denen ich Angst hatte, Fehler zu machen, dann scheint mir hinterher meistens, daß die Angst",
    options: [
      { category: "völlig begründet war", points: 0 },
      { category: "einen nachteiligen Effekt hatte", points: 0 },
      { category: "mich kaum beeinträchtigt hat", points: 1, group: "F+"},
      { category: "mir sogar geholfen hat", points: 1, group: "F+"},
    ],
  },
  {
    number: 55,
    text: "Während des Unterrichts in der Schule",
    options: [
      { category: "paßte ich meistens gut auf, was gesagt wurde", points: 1, group: "L2" },
      { category: "hatte ich manchmal Schwierigkeiten, bei der Sache zu sein", points: 0 },
      { category: "schweiften meine Gedanken oft zu ganz anderen Dingen ab", points: 0 },
    ],
  },
  {
    number: 56,
    text: "Wenn ich beim Lernen und Arbeiten unterbrochen werde, dann ist das für mich",
    options: [
      { category: "störend", points: 1, group: "L1" },
      { category: "nicht so schlimm", points: 0 },
      { category: "eine angenehme Abwechslung", points: 0 },
    ],
  },
];

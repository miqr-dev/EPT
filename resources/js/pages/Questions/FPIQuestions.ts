export interface FPIQuestion {
  number: number;
  text: string;
  stimmt: { category: string | number; points: number }[];
  stimmtNicht: { category: string | number; points: number }[];
}

export const FPI_QUESTIONS: FPIQuestion[] = [

  {
    number: 2,
    text: "Ich gehe abends gerne aus",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 3,
    text: "Ich habe (hatte) einen Beruf, der mich voll befriedigt.",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 4,
    text: "Ich habe fast immer eine schlagfertige Antwort bereit",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: [{ category: 4, points: 1 }]
  },
  {
    number: 5,
    text: "Ich glaube, dass ich mir beim Arbeiten mehr Mühe gebe als die meisten anderen Menschen",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 6,
    text: "Ich scheue mich, allein in einen Raum zu gehen, in dem andere Leute bereits zusammensitzen und sich unterhalten",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 7,
    text: "Manchmal bin ich zu spät zu einer Verabredung oder zur Schule gekommen",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 8,
    text: "Ich würde mich beim Kellner oder Geschäftsführer eines Restaurants beschweren, wenn ein schlechtes Essen serviert wird",
    stimmt: [],
    stimmtNicht: [{ category: 4, points: 1 }]
  },
  {
    number: 9,
    text: "Ich habe manchmal hässliche Bemerkungen über andere Menschen gemacht",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 10,
    text: "Im Krankheitsfall möchte ich Befund und Behandlung eigentlich von einem zweiten Arzt überprüfen lassen",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 11,
    text: "Ich bin ungern mit Menschen zusammen, die ich noch nicht kenne",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 12,
    text: "Wenn jemand meinem Freund etwas Böses tut, bin ich dabei, wenn es heimgezahlt wird",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 13,
    text: "Meine Bekannten halten mich für einen energischen Menschen",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 14,
    text: "Ich würde kaum zögern, auch alte und schwerbehinderte Menschen zu pflegen",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 15,
    text: "Ich kann mich erinnern, mal so zornig gewesen zu sein, dass ich das nächstbeste Ding nahm und es zerriss oder zerschlug",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 16,
    text: "Ich habe häufig Kopfschmerzen",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 17,
    text: "Ich bin unternehmungslustiger als die meisten meiner Bekannten",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 18,
    text: "Ich achte aus gesundheitlichen Gründen auf regelmäßige Mahlzeiten und reichlichen Schlaf",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 19,
    text: "Ich habe manchmal ein Gefühl der Teilnahmslosigkeit und inneren Leere",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 20,
    text: "Sind wir in ausgelassener Runde, so überkommt mich oft eine Lust zu groben Streichen",
    stimmt: [{ category: 6, points: 1 }, { category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 21,
    text: "Ich bin leicht beim Ehrgeiz zu packen",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 22,
    text: "Ich bin der Ansicht, die Menschen in den Entwicklungsländern sollten sich zuerst einmal selbst helfen",
    stimmt: [],
    stimmtNicht: [{ category: 2, points: 1 }]
  },
  {
    number: 23,
    text: "Ich lebe mit mir selbst in Frieden und ohne innere Konflikte",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 24,
    text: "Ich male mir manchmal aus, wie übel es denen eigentlich ergehen müsste, die mir Unrecht tun",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 25,
    text: "In einer vergnügten Gesellschaft kann ich mich meistens ungezwungen und unbeschwert auslassen",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 26,
    text: "Ich fühle mich auch über meine Familie hinaus für andere Menschen verantwortlich",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 27,
    text: "Ich neige dazu, bei Auseinandersetzungen lauter zu sprechen als sonst",
    stimmt: [{ category: 5, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 28,
    text: "Ich bin oft nervös, weil zu viel auf mich einströmt",
    stimmt: [{ category: 7, points: 1 }, { category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 29,
    text: "Wenn ich noch einmal geboren würde, dann würde ich nicht anders leben wollen",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 30,
    text: "Wenn mir einmal etwas schiefgeht, regt mich das nicht weiter auf",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 31,
    text: "Ich habe mich über die häufigsten Krankheiten und ihre ersten Anzeichen informiert",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 32,
    text: "Ich übernehme bei gemeinsamen Untemehmungen gern die Führung",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 33,
    text: "Ich habe selbst bei warmem Wetter häufiger kalte Hände und Füße",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 34,
    text: "Ich finde, jeder Mensch soll sehen, wie er zurecht kommt",
    stimmt: [],
    stimmtNicht: [{ category: 2, points: 1 }]
  },
  {
    number: 35,
    text: "Die täglichen Belastungen sind oft so groß, dass ich davon oft müde und erschöpft bin",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 36,
    text: "Ich denke oft, dass ich meinen Konsum einschränken müsste, um dann an benachteiligte Menschen abzugeben",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 37,
    text: "Als Kind habe ich manchmal ganz gerne anderen die Arme umgedreht, an den Haaren gezogen, ein Bein gestellt usw.",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 38,
    text: "Um gesund zu bleiben, achte ich auf ein ruhiges Leben",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 39,
    text: "Ich habe gern mit Aufgaben zu tun, die schnelles Handeln verlangen",
    stimmt: [{ category: 3, points: 1 }, { category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 40,
    text: "Es macht mir Spaß, anderen Fehler nachzuweisen",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 41,
    text: "Wenn jemand weint, möchte ich ihn am liebsten umarmen und trösten",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 42,
    text: "Meine Familie und meine Bekannten können mich im Grunde kaum richtig verstehen",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 43,
    text: "Es gibt für mich noch eine Menge sinnvoller Aufgaben, die ich in der Zukunft anpacken werde",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 44,
    text: "Ich pflege schnell und sicher zu handeln",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 45,
    text: "Ich fühle mich oft wie ein Pulverfass kurz vor der Explosion",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 46,
    text: "Ich hätte gern mehr Zeit für mich ohne so viele Verpflichtungen",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 47,
    text: "Ich habe manchmal das Gefühl, einen Kloß im Hals zu haben",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 48,
    text: "Mit anderen zu wetteifern, macht mir Spaß",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 49,
    text: "Termindruck und Hektik lösen bei mir körperliche Beschwerden aus",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 50,
    text: "Wenn ich Zuflucht zu körperlicher Gewalt nehmen muss, um mein Recht zu verteidigen, so tue ich es",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 51,
    text: "Ich habe manchmal Hitzewallungen und Blutandrang zum Kopf",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 52,
    text: "Auch wenn es viel zu tun gibt, lasse ich mich nicht hetzen",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 53,
    text: "Ich kann in eine ziemlich langweilige Gesellschaft schnell Leben bringen",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 54,
    text: "Bei wichtigen Dingen bin ich bereit, mit anderen energisch zu konkurrieren",
    stimmt: [{ category: 3, points: 1 }, { category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 55,
    text: "Ich mache mir oft Sorgen um meine Gesundheit",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 56,
    text: "Wenn mich jemand anschreit, schreie ich zurück",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 57,
    text: "Mein Herz beginnt manchmal zu jagen und unregelmäßig zu schlagen",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 58,
    text: "In meinem bisherigen Leben habe ich kaum das verwirklichen können, was in mir steckt",
    stimmt: [],
    stimmtNicht: [{ category: 1, points: 1 }]
  },
  {
    number: 59,
    text: "Ich würde mich selbst eher als gesprächig bezeichnen",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 60,
    text: "Auch wenn mich etwas sehr aus der Fassung bringt, beruhige ich mich meistens wieder rasch",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 61,
    text: "Die beruflichen Aufgaben sind mir oft wichtiger als viel Freizeit oder interessante Hobbies",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 62,
    text: "Ich vermeide es, ungewaschenes Obst zu essen",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 63,
    text: "Es fällt mir schwer, vor einer großen Gruppe von Menschen zu sprechen oder vorzutragen",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 64,
    text: "Auch an Wochenenden bin ich stark eingespannt",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 65,
    text: "Ich vermeide Zugluft, weil man sich zu leicht erkälten kann",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 66,
    text: "Manchmal schiebe ich etwas auf, was ich sofort tun sollte",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 67,
    text: "Ich habe häufiger Verstopfung",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 68,
    text: "Wenn jemand in meine Richtung hustet oder niest, versuche ich mich abzuwenden",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 69,
    text: "Ich bin hin und wieder ein wenig schadenfroh",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 70,
    text: "Ich hole sicherheitshalber ärztlichen Rat ein, wenn ich länger als zwei Tage erhöhte Temperatur (leichtes Fieber) habe",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 71,
    text: "Hin und wieder gebe ich ein bisschen an",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 72,
    text: "Ich bemerke häufiger ein unwillkürliches Zucken, z.B. um meine Augen",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 73,
    text: "Ich bin im Grunde eher ein ängstlicher Mensch",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 74,
    text: "Ich habe Spaß an schwierigen Aufgaben, die mich herausfordern",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 75,
    text: "Ich habe Schwierigkeiten einzuschlafen und durchzuschlafen",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 76,
    text: "Ich bin ziemlich lebhaft",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 77,
    text: "Manchmal bin ich beleidigt, wenn es nicht nach meinem Willen geht",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 78,
    text: "Ich spreche oft Drohungen aus, die ich gar nicht ernst meine",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 79,
    text: "Ich bin häufiger abgespannt, matt und erschöpft",
    stimmt: [{ category: 7, points: 1 },{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 80,
    text: "Ich bekomme häufig ein schlechtes Gewissen, wenn ich sehe, wie schlecht es anderen Menschen geht",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 81,
    text: "Ich schließe nur langsam Freundschaften",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: [{ category: 'E', points: 1 }]
  },
  {
    number: 82,
    text: "Manchmal habe ich ohne eigentlichen Grund ein Gefühl unbestimmter Gefahr oder Angst",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 83,
    text: "Meine Tischmanieren sind zu Hause schlechter als im Restaurant",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 84,
    text: "Weil man sich so leicht anstecken kann, wasche ich mir zu Hause gleich die Hände",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 85,
    text: "Ich werde ziemlich leicht verlegen",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 86,
    text: "Mein Blut kocht, wenn man mich zum Narren hält",
    stimmt: [{ category: 5, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 87,
    text: "Wenn mich ein Fremder um eine kleine Geldspende bittet, ist mir das ziemlich lästig",
    stimmt: [],
    stimmtNicht: [{ category: 2, points: 1 }]
  },
  {
    number: 88,
    text: "Ich bin immer guter Laune",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 89,
    text: "Ich passe auf, dass ich nicht zu viel Autoabgase und Staub einatme",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 90,
    text: "Wenn ich wirklich wütend werde, bin ich in der Lage, jemandem eine runterzuhauen",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 91,
    text: "Ich spiele anderen Leuten gern einen harmlosen Streich",
    stimmt: [{ category: 'E', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 92,
    text: "Ich habe einen empfindlichen Magen",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 93,
    text: "Es gibt nur wenige Dinge, die mich leicht erregen oder ärgern",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 94,
    text: "Oft habe ich alles gründlich satt",
    stimmt: [],
    stimmtNicht: [{ category: 1, points: 1 }]
  },
  {
    number: 95,
    text: "Manchmal habe ich Gedanken, über die ich mich schämen muss",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 96,
    text: "Nur selten kann ich richtig abschalten",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 97,
    text: "Ich erröte leicht",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 98,
    text: "Einem Menschen, der mich schlecht behandelt oder beleidigt hat, wünsche ich eine harte Strafe",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 99,
    text: "Meine Hände sind häufiger zittrig, z.B. beim Anzünden einer Zigarette oder Halten einer Tasse",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 100,
    text: "Ich bin selten in bedrückter, unglücklicher Stimmung",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 101,
    text: "Ich ziehe das Handeln dem Pläneschmieden vor",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 102,
    text: "Im Allgemeinen bin ich ruhig und nicht leicht aufzuregen",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 103,
    text: "Vor lauter Aufgaben und Zeitdruck bin ich manchmal ganz durcheinander",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 104,
    text: "Wenn ich irgendwo zu Gast bin, ist mein Benehmen meistens besser als zu Hause",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 105,
    text: "Ich kann oft meinen Ärger und meine Wut nicht beherrschen",
    stimmt: [{ category: 5, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 106,
    text: "Es gibt Zeiten, in denen ich ganz traurig und niedergedrückt bin",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 107,
    text: "Ab und zu erzähle ich auch einmal eine Lüge",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 108,
    text: "Ich lasse mich durch eine Vielzahl von kleinen Störungen nicht aus der Ruhe bringen",
    stimmt: [],
    stimmtNicht: [{ category: 5, points: 1 }]
  },
  {
    number: 109,
    text: "Bei Geselligkeiten und öffentlichen Veranstaltungen bleibe ich lieber im Hintergrund",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: [{ category: 'E', points: 1 }]
  },
  {
    number: 110,
    text: "Ich träume tagsüber oft von Dingen, die doch nicht verwirklicht werden können",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 111,
    text: "Ich gebe gelegentlich Geld und Spenden für Katastrophenhilfe, Caritas, Brot für die Welt und andere Sammlungen",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 112,
    text: "Ich grüble viel über mein bisheriges Leben nach",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: [{ category: 1, points: 1 }]
  },
  {
    number: 113,
    text: "Ich neige oft zu Hast und Eile, auch wenn es überhaupt nicht notwendig ist",
    stimmt: [{ category: 5, points: 1 }],
    stimmtNicht: []
  },
    {
    number: 114,
    text: "Ich spreche manchmal über Dinge, von denen ich nichts verstehe",
    stimmt: [{ category: 10, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 115,
    text: "Oft rege ich mich zu rasch über jemanden auf",
    stimmt: [{ category: 5, points: 1 },{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 116,
    text: "Ich denke manchmal, dass ich mich mehr schonen sollte",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 117,
    text: "Handtücher in viel benutzten Waschräumen sind mir wegen der Ansteckungsgefahr unangenehm",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 118,
    text: "Ich arbeite oft unter Zeitdruck",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 119,
    text: "Ich bin mit meinen gegenwärtigen Lebensbedingungen oft unzufrieden",
    stimmt: [],
    stimmtNicht: [{ category: 1, points: 1 }]
  },
  {
    number: 120,
    text: "Beim Reisen schaue ich lieber auf die Landschaft als mich mit den Mitreisenden zu unterhalten",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 121,
    text: "Da der Staat schon für Sozialhilfe sorgt, brauche ich im Einzelnen nicht zu helfen",
    stimmt: [],
    stimmtNicht: [{ category: 2, points: 1 }]
  },
  {
    number: 122,
    text: "Die Anforderungen, die an mich gestellt werden, sind oft zu hoch",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 123,
    text: "Mein Körper reagiert deutlich auf Wetteränderung",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 124,
    text: "Es fällt mir schwer, den richtigen Gesprächsstoff zu finden, wenn ich jemanden kennenlernen will",
    stimmt: [{ category: 4, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 125,
    text: "Ich denke manchmal, dass ich zu viel arbeite",
    stimmt: [{ category: 7, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 126,
    text: "Meine Laune wechselt ziemlich oft",
    stimmt: [{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 127,
    text: "Auch ohne ernste Beschwerden gehe ich regelmäßig zum Arzt, nur zur Vorsicht",
    stimmt: [{ category: 9, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 128,
    text: "Alles in allem bin ich ausgesprochen zufrieden mit meinem bisherigen Leben",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 129,
    text: "Bei meiner Arbeit bin ich meist schneller als andere",
    stimmt: [{ category: 3, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 130,
    text: "Ich habe häufig das Gefühl, im Stress zu sein",
    stimmt: [{ category: 7, points: 1 },{ category: 'N', points: 1 }],
    stimmtNicht: []
  },
  {
    number: 131,
    text: "Meine Partnerbeziehung (Ehe) ist gut",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 132,
    text: "Lieber bis zum Äußersten gehen als feige zu sein",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 133,
    text: "Ich habe manchmal ein Gefühl erstickender Enge in der Brust",
    stimmt: [{ category: 8, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 134,
    text: "Ich habe schon unbezahlt beim Roten Kreuz, in meiner Gemeinde oder in anderen sozialen Einrichtungen geholfen",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 135,
    text: "Ich bin leicht aus der Ruhe gebracht, wenn ich angegriffen werde",
    stimmt: [{ category: 5, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 136,
    text: "Ich nehme mir viel Zeit, anderen Menschen geduldig zuzuhören, wenn sie von ihren Sorgen erzählen",
    stimmt: [{ category: 2, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 137,
    text: "Es gab Leute, die mich so ärgerten, dass es zu einer handfesten Auseinandersetzung kam",
    stimmt: [{ category: 6, points: 1 }],
    stimmtNicht: []
  },
  {
    number: 138,
    text: "Meistens blicke ich voller Zuversicht in die Zukunft",
    stimmt: [{ category: 1, points: 1 }],
    stimmtNicht: []
  }

];
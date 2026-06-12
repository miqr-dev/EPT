export interface InstructionFeature {
    label: string;
    value: string;
}

export interface InstructionSection {
    title: string;
    description?: string;
    items: string[];
}

export interface InstructionGuideItem {
    label?: string;
    text: string;
}

export interface InstructionGuideBlock {
    title?: string;
    description?: string;
    steps?: string[];
    items?: InstructionGuideItem[];
    note?: string;
}

export interface InstructionGuideSection {
    title: string;
    description?: string;
    blocks: InstructionGuideBlock[];
}

export interface TestInstruction {
    name: string;
    fullName: string;
    summary: string;
    variants: string[];
    features: InstructionFeature[];
    beforeTest: string[];
    duringTest: string[];
    afterTest: string[];
    guideSections?: InstructionGuideSection[];
    sections?: InstructionSection[];
    resultImage?: {
        src: string;
        alt: string;
        caption: string;
    };
}

export const testInstructions: Record<string, TestInstruction> = {
    allgemein: {
        name: 'Allgemeine Informationen',
        fullName: 'Leitfaden für Lehrkräfte und Prüfende',
        summary:
            'Dieser Leitfaden beschreibt den vollständigen organisatorischen Ablauf: Teilnehmende importieren, Prüfungen vorbereiten, Verträge freigeben, laufende Tests steuern und Ergebnisse abrufen.',
        variants: [],
        features: [],
        beforeTest: [],
        duringTest: [],
        afterTest: [],
        guideSections: [
            {
                title: 'Prüfungsvorbereitung',
                description: 'Teilnehmende importieren, eine Prüfung zusammenstellen und den Prüfungsraum vorbereiten.',
                blocks: [
                    {
                        title: 'Teilnehmende importieren',
                        description: 'Damit eine Anmeldung möglich ist, müssen die Teilnehmenden zuerst in das EPT importiert werden.',
                        steps: [
                            'Navigieren Sie zu „Benutzer importieren“.',
                            'Tragen Sie den Benutzernamen ein oder kopieren Sie ihn aus der Teilnehmerliste in VerBIS/ServSmt.',
                            'Die Rolle „Teilnehmer“ und die Option „Login erlauben“ sind standardmäßig ausgewählt.',
                            'Klicken Sie auf den blauen Button „Importieren“.',
                        ],
                        note: 'Alle neu importierten Personen erscheinen anschließend in der Tabelle „Importierte Benutzer“. Danach können Sie zum Dashboard wechseln und die Prüfung vorbereiten.',
                    },
                    {
                        title: 'Neue Prüfung erstellen',
                        steps: [
                            'Klicken Sie auf „+ Neue Prüfung erstellen“.',
                            'Vergeben Sie einen eindeutigen Namen, zum Beispiel „24.02.2026 Gruppe A“.',
                            'Wählen Sie die Teilnehmenden aus „Aktuelle Benutzer“ oder „Importierte Benutzer“. Über das Kontrollkästchen neben „Name“ können alle Personen ausgewählt werden.',
                            'Übernehmen Sie die ausgewählten Personen mit dem Pfeil-Button in die Prüfungsliste.',
                            'Wählen Sie unter „Prüfungsschritte“ die Tests aus, die zugewiesen werden sollen.',
                            'Klicken Sie auf „Prüfung speichern“.',
                        ],
                    },
                    {
                        title: 'Prüfung starten',
                        steps: [
                            'Nach dem Speichern erscheint die Prüfung rechts in der Tabelle „Alle Prüfungen“.',
                            'Öffnen Sie „Details“ und kontrollieren Sie die Teilnehmenden sowie die zugewiesenen Tests.',
                            'Vor dem Prüfungsstart können Teilnehmende und Tests noch hinzugefügt oder entfernt werden. Angemeldete Personen werden grün, nicht angemeldete Personen rot angezeigt.',
                            'Speichern Sie Änderungen über „Änderungen speichern“.',
                            'Klicken Sie auf „Prüfung starten“.',
                        ],
                    },
                ],
            },
            {
                title: 'Vertrag anzeigen',
                description: 'Den Maßnahmevertrag kontrollieren, für Teilnehmende freigeben und anschließend wieder sperren.',
                blocks: [
                    {
                        title: 'Vertrag vorab kontrollieren',
                        description:
                            'Nachdem der Maßnahmevertrag im vorgesehenen Ordner gespeichert wurde, kontrollieren Sie über das Augen-Symbol neben dem Namen, ob die Datei vorhanden und für die teilnehmende Person lesbar ist.',
                    },
                    {
                        title: 'Vertrag freigeben',
                        items: [
                            {
                                label: 'Vor dem Prüfungsstart',
                                text: 'Geben Sie den Vertrag für einzelne Personen über „Vertrag freigeben“ neben dem jeweiligen Namen frei.',
                            },
                            {
                                label: 'Während der laufenden Prüfung',
                                text: 'In der Tabelle „Laufende Prüfungen“ können Sie den Vertrag oberhalb der Testnamen gleichzeitig für alle Teilnehmenden freigeben.',
                            },
                            {
                                label: 'Nach der Einsicht',
                                text: 'Klicken Sie auf „Vertragsansicht sperren“, sobald alle Teilnehmenden die Prüfung des Vertrags abgeschlossen haben.',
                            },
                        ],
                    },
                ],
            },
            {
                title: 'Während der Prüfung',
                description: 'Tests freischalten, Bearbeitungsstände beobachten und bei Bedarf eingreifen.',
                blocks: [
                    {
                        title: 'Tabelle „Laufende Prüfungen“',
                        description:
                            'Sobald die Prüfung gestartet wurde, erscheint auf dem Dashboard die Tabelle „Laufende Prüfungen“. Ein Klick auf einen Testnamen aktiviert den Button „Test starten“ auf den Rechnern der Teilnehmenden.',
                        items: [
                            { label: 'Aktueller Test', text: 'Name des derzeit freigeschalteten Tests.' },
                            {
                                label: 'Status',
                                text: 'Mögliche Zustände sind „Nicht gestartet“, „In Bearbeitung“, „Pausiert“ und „Abgeschlossen“.',
                            },
                            { label: 'Gesamtzeit', text: 'Die für den Test standardmäßig hinterlegte Bearbeitungszeit.' },
                            {
                                label: 'Verbleibende Zeit',
                                text: 'Individueller Countdown. Er startet erst, wenn die teilnehmende Person nach dem Lesen der Anleitung auf „Test starten“ klickt.',
                            },
                        ],
                    },
                    {
                        title: 'Zeit, Pause und Testende steuern',
                        items: [
                            {
                                label: 'Zusatzzeit',
                                text: 'Fügen Sie Zusatzzeit vor Ablauf des Countdowns hinzu. Pro Vorgang sind maximal 30 Minuten möglich; der Vorgang kann mehrfach wiederholt werden.',
                            },
                            {
                                label: 'Pausierbare Tests',
                                text: 'Ein pausierter Test kann später wieder aufgerufen und fortgesetzt werden. Vor dem Beenden der gesamten Prüfung müssen alle pausierten Tests aller Teilnehmenden abgeschlossen werden, sonst gehen sie verloren.',
                            },
                            {
                                label: 'Test beenden',
                                text: 'Der rote Button „Test beenden“ startet einen Countdown von zehn Sekunden. Danach wird der Test beendet und geschlossen.',
                            },
                            {
                                label: 'Nächster Test',
                                text: 'Wenn alle Teilnehmenden den aktuellen Test beendet haben, schalten Sie den nächsten Test durch Anklicken seines Namens frei.',
                            },
                        ],
                    },
                    {
                        title: 'Farben der Testnamen',
                        items: [
                            { label: 'Blau', text: 'Der Test wurde noch nicht gestartet.' },
                            { label: 'Grün', text: 'Der Test ist aktuell freigeschaltet beziehungsweise läuft.' },
                            { label: 'Schwarz', text: 'Der Test ist abgeschlossen.' },
                        ],
                    },
                    {
                        title: 'Prüfung abschließen',
                        description:
                            'Nachdem alle Tests beendet wurden, kann die Prüfung nach zwei Tagen über den schwarzen Button „Prüfung beenden“ oben rechts in der Tabelle geschlossen werden.',
                    },
                ],
            },
            {
                title: 'Prüfungsergebnisse',
                description: 'Einzelergebnisse kontrollieren, Antworten einsehen und PDF-Dateien herunterladen.',
                blocks: [
                    {
                        title: 'Übersicht',
                        description:
                            'Die Seite „Prüfungsergebnisse“ zeigt den Namen der teilnehmenden Person, das Vorbereitungsdatum der Prüfung, die abgeschlossenen Tests und die verfügbaren PDF-Downloads.',
                        items: [
                            {
                                label: 'Suche',
                                text: 'Über das Suchfeld oben rechts kann nach Vor- oder Nachname gesucht werden.',
                            },
                            {
                                label: 'Verfügbarkeit',
                                text: 'Abgeschlossene Tests erscheinen sofort auf der Ergebnisseite. Die gesamte Prüfung muss dafür nicht beendet sein.',
                            },
                        ],
                    },
                    {
                        title: 'Ein Ergebnis öffnen',
                        steps: [
                            'Klicken Sie auf den Testnamen, um die Ergebnisseite zu öffnen.',
                            'Öffnen Sie den zunächst eingeklappten Bereich „Antworten“, um die abgegebenen Antworten anzuzeigen.',
                            'Laden Sie oben links entweder nur das Ergebnis oder das Ergebnis einschließlich Antworten herunter.',
                            'Schließen Sie die Ergebnisansicht über das rote „X“ oben rechts.',
                        ],
                    },
                    {
                        title: 'Alle Ergebnisse herunterladen',
                        items: [
                            { label: 'Alle Tests', text: 'Lädt alle vorhandenen Testergebnisse ohne Antworten als PDF herunter.' },
                            {
                                label: 'Mit Antworten',
                                text: 'Lädt alle vorhandenen Testergebnisse einschließlich Antworten als PDF herunter.',
                            },
                        ],
                    },
                ],
            },
            {
                title: 'Kollaboration',
                description: 'Informationen zu den Kollaborationsfunktionen werden in Kürze ergänzt.',
                blocks: [],
            },
        ],
    },
    brt: {
        name: 'BRT',
        fullName: 'Hinweise für BRT-A und BRT-B',
        summary:
            'Die Teilnehmenden bearbeiten die Aufgaben frei über die linke Navigation. Besondere Aufmerksamkeit gilt der Eingabe von Brüchen und der anschließenden Ergebniskontrolle.',
        variants: ['BRT-A', 'BRT-B'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Eingabe', value: 'Max. 6 Ziffern und 3 weitere Zeichen' },
            { label: 'Zeitlimit', value: '35 Minuten' },
        ],
        beforeTest: [
            'Informieren Sie die Teilnehmenden, dass sie über die linke Seitenleiste frei zwischen den Aufgaben navigieren können.',
            'Erklären Sie, dass Brüche mit „/“ eingegeben werden. Der Schrägstrich wird mit „Umschalt + 7“ geschrieben.',
        ],
        duringTest: [
            'Die Teilnehmenden können Aufgaben überspringen und später über die linke Seitenleiste zurückkehren.',
            'Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.',
        ],
        afterTest: [
            'Kontrollieren Sie die Antworten jedes Teilnehmenden nach dem Test sorgfältig.',
            'Korrigieren Sie fehlerhaft ausgewertete Antworten direkt auf der Ergebnisseite und speichern Sie die Änderungen.',
        ],
    },
    mrt: {
        name: 'MRT',
        fullName: 'Hinweise für MRT-A und MRT-B',
        summary:
            'Beim MRT wird eine Antwort durch zweimaliges Anklicken bestätigt. Erst die Bestätigung speichert die Auswahl und führt zur nächsten Frage.',
        variants: ['MRT-A', 'MRT-B'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle Fragen' },
            { label: 'Zeitlimit', value: '30 Minuten' },
        ],
        beforeTest: [
            'Informieren Sie die Teilnehmenden ausdrücklich über die Bedienung per Doppelklick beziehungsweise zweimaligem Anklicken.',
            'Erklären Sie: Der erste Klick markiert eine Option, der zweite Klick auf dieselbe Option speichert die Antwort und öffnet die nächste Frage.',
        ],
        duringTest: [
            'Die nächste Frage erscheint erst, nachdem eine Option ausgewählt und mit einem zweiten Klick bestätigt wurde.',
            '„Weiter“ und „Zurück“ dienen ausschließlich zur Navigation zwischen bereits beantworteten Fragen.',
            'Mit „Zurück“ können gespeicherte Antworten kontrolliert werden. Eine Änderung erfolgt durch zweimaliges Anklicken einer anderen Option.',
            'Alle Fragen müssen beantwortet werden.',
        ],
        afterTest: [],
    },
    'fpi-r': {
        name: 'FPI-R',
        fullName: 'Hinweise zur Einwilligung und Vollständigkeit',
        summary:
            'Vor dem Start ist eine Zustimmung erforderlich. Während der Bearbeitung müssen alle Fragen mit Ausnahme der optionalen Frage 131 beantwortet werden.',
        variants: ['FPI-R'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle außer Frage 131' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [
            'Die teilnehmende Person muss der Erklärung zustimmen, um den Test fortsetzen zu können.',
            'Wenn keine Zustimmung erteilt wird, muss die Person mit „Esc“ den Vollbildmodus verlassen und bestätigen, dass der Test abgebrochen wird.',
            'In diesem Fall gilt der Test im EPT als nicht durchgeführt. Die Lehrkraft kann anschließend den nächsten Test starten.',
        ],
        duringTest: [
            'Alle Fragen müssen beantwortet werden; nur Frage 131 ist optional.',
            'Unter Frage 131 erscheint der Hinweis „gegebenenfalls freilassen“.',
            'Nicht beantwortete Fragen werden in der linken Seitenleiste angezeigt. Ein Klick auf die Nummer führt direkt zur ausgelassenen Frage.',
        ],
        afterTest: [],
    },
    lmt: {
        name: 'LMT',
        fullName: 'Hinweise zur Testdurchführung',
        summary: 'Beim LMT müssen die Teilnehmenden sämtliche Fragen beantworten, bevor der Test regulär abgeschlossen werden kann.',
        variants: ['LMT'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle Fragen' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [],
        duringTest: [],
        afterTest: [],
    },
    lps: {
        name: 'LPS-B',
        fullName: 'Spaltenweise Durchführung und manuelle Auswertung',
        summary:
            'Die Spalten werden einzeln und unter Anleitung der Lehrkraft gestartet. LPS-B läuft ausschließlich im Hellmodus und kann nach dem Start nicht pausiert werden.',
        variants: ['LPS-B'],
        features: [
            { label: 'Darstellung', value: 'Nur Hellmodus' },
            { label: 'Pause', value: 'Nicht pausierbar' },
            { label: 'Start', value: 'Spalten einzeln starten' },
            { label: 'Zeitlimit', value: '180 Minuten' },
        ],
        beforeTest: [
            'Erklären Sie vor jeder Spalte genau, was die Teilnehmenden bearbeiten sollen.',
            'Warnen Sie die Teilnehmenden, den Start-Button der nächsten Spalte erst anzuklicken, nachdem Ihre Erklärung beendet ist.',
            'Stellen Sie für Spalte 6 Papier und Schreibmaterial bereit.',
        ],
        duringTest: [
            'Die Teilnehmenden starten die einzelnen Spalten selbst über den jeweiligen Start-Button.',
            'Beim Start von Spalte 2 werden Spalte 1 und Spalte 2 gemeinsam aktiviert.',
            'Spalte 6 wird auf Papier bearbeitet und nicht direkt im Test eingegeben.',
        ],
        afterTest: [
            'Tragen Sie die erreichte Punktzahl für Spalte 6 auf der LPS-B-Ergebnisseite in das Feld „Spalte 6 (manuell)“ ein.',
            'Klicken Sie anschließend auf „Speichern“. Das Auswertungsdiagramm wird automatisch mit dem neuen Wert aktualisiert.',
        ],
        resultImage: {
            src: '/images/instructions/lps-b-manual-score.svg',
            alt: 'LPS-B-Ergebnisseite mit Eingabefeld für die manuelle Punktzahl der Spalte 6 und Speichern-Schaltfläche',
            caption: 'Manuelle Eingabe der Punktzahl für Spalte 6 auf der LPS-B-Ergebnisseite.',
        },
    },
    'bit-2': {
        name: 'BIT-2',
        fullName: 'Hinweise zur Testdurchführung',
        summary: 'Beim BIT-2 müssen alle Aufgaben beantwortet werden. Der Test unterstützt Hell- und Dunkelmodus und kann pausiert werden.',
        variants: ['BIT-2'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle Aufgaben' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [],
        duringTest: [],
        afterTest: [],
    },
    bt: {
        name: 'BT',
        fullName: 'Durchführung, manuelle Bewertung und Ergebniskontrolle',
        summary:
            'Der BT enthält sechs unterschiedlich bediente Aufgaben. Einzelne Teile werden auf Papier bearbeitet oder manuell bewertet und müssen auf der Ergebnisseite nachgetragen beziehungsweise kontrolliert werden.',
        variants: ['BT'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Auswertung', value: 'Teilweise manuell' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [
            'Stellen Sie Papier und Schreibmaterial für Aufgabe 2 bereit.',
            'Erklären Sie vor Beginn die Drag-and-Drop-Bedienung für die Aufgaben 1 und 6.',
        ],
        duringTest: [
            'Achten Sie auf die unterschiedlichen Eingaberegeln der sechs Aufgaben.',
            'Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.',
        ],
        afterTest: [
            'Tragen Sie die manuell ermittelte Punktzahl für Aufgabe 2 auf der BT-Ergebnisseite ein.',
            'Kontrollieren Sie die Auswertung von Aufgabe 5 nach jeder Durchführung und korrigieren Sie sie gegebenenfalls.',
            'Die Browseransicht zeigt detailliert, wie Punkte für die einzelnen Aufgaben vergeben oder abgezogen wurden. Diese Details erscheinen nicht im PDF-Export.',
        ],
        sections: [
            {
                title: 'Aufgabe 1',
                items: ['Drag-and-Drop-Aufgabe.'],
            },
            {
                title: 'Aufgabe 2',
                items: [
                    'Die Aufgabe wird auf Papier bearbeitet.',
                    'Die Bewertung erfolgt manuell.',
                    'Die erreichte Punktzahl wird anschließend manuell auf der BT-Ergebnisseite eingetragen.',
                ],
            },
            {
                title: 'Aufgabe 3',
                items: ['Es können ausschließlich Zahlen eingegeben werden.', 'Maximal vier Stellen sind zulässig.'],
            },
            {
                title: 'Aufgabe 4',
                items: ['Es können ausschließlich Buchstaben eingegeben werden.', 'Maximal sechs Zeichen sind zulässig.'],
            },
            {
                title: 'Aufgabe 5',
                items: [
                    'Antworten können ausschließlich durch Anklicken als „X“ markiert werden.',
                    'Die Auswertung muss nach jeder Durchführung kontrolliert und gegebenenfalls korrigiert werden.',
                ],
            },
            {
                title: 'Aufgabe 6',
                items: [
                    'Drag-and-Drop-Aufgabe.',
                    'Bleibt das Feld „Weg/Zeit“ leer, wertet das EPT den Eintrag automatisch als „telefonisch“.',
                    'Die verschiedenen Antwortvarianten werden automatisch nach der hinterlegten Bewertungslogik bepunktet.',
                ],
            },
        ],
    },
    avem: {
        name: 'AVEM',
        fullName: 'Hinweise zur Testdurchführung',
        summary: 'Beim AVEM müssen alle Fragen beantwortet werden. Der Test unterstützt Hell- und Dunkelmodus und kann pausiert werden.',
        variants: ['AVEM'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle Fragen' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [],
        duringTest: [],
        afterTest: [],
    },
    '628': {
        name: '628',
        fullName: 'Hinweise zur Testdurchführung',
        summary: 'Beim Test 628 müssen alle Aufgaben beantwortet werden. Der Test unterstützt Hell- und Dunkelmodus und kann pausiert werden.',
        variants: ['628'],
        features: [
            { label: 'Darstellung', value: 'Hell- und Dunkelmodus' },
            { label: 'Pause', value: 'Pausierbar' },
            { label: 'Antwortpflicht', value: 'Alle Aufgaben' },
            { label: 'Zeitlimit', value: '60 Minuten' },
        ],
        beforeTest: [],
        duringTest: [],
        afterTest: [],
    },
};

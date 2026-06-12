export interface InstructionFeature {
    label: string;
    value: string;
}

export interface InstructionSection {
    title: string;
    description?: string;
    items: string[];
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
            'Diese Anleitungen beschreiben, wie Teilnehmende die einzelnen Tests bearbeiten und welche Hinweise vor, während und nach der Durchführung wichtig sind.',
        variants: [],
        features: [
            { label: 'Zielgruppe', value: 'Lehrkräfte und Prüfende' },
            { label: 'Aufbau', value: 'Vor, während und nach dem Test' },
            { label: 'Ergänzung', value: 'Testspezifische Hinweise beachten' },
        ],
        beforeTest: [
            'Öffnen Sie die Anleitung des vorgesehenen Tests und erklären Sie den Teilnehmenden die dort genannten Bedienhinweise, bevor der Test gestartet wird.',
            'Stellen Sie benötigte Hilfsmittel bereit. Bei Tests mit Papieraufgaben müssen Papier und Schreibmaterial vor Beginn verfügbar sein.',
            'Weisen Sie die Teilnehmenden darauf hin, erst nach Ihrer Freigabe auf „Test starten“ beziehungsweise auf den nächsten Start-Button zu klicken.',
        ],
        duringTest: [
            'Achten Sie darauf, dass der richtige Test geöffnet ist und die Teilnehmenden die beschriebenen Eingabe- und Navigationsregeln einhalten.',
            'Eine Pause ist nur bei den Tests möglich, die in der jeweiligen Anleitung als „pausierbar“ gekennzeichnet sind.',
            'Der Hell- oder Dunkelmodus kann nur genutzt werden, wenn beide Darstellungen für den jeweiligen Test ausgewiesen sind.',
        ],
        afterTest: [
            'Prüfen Sie bei Tests mit manuellen Bewertungen oder Korrekturmöglichkeiten die Ergebnisse direkt in der Browseransicht.',
            'Speichern Sie manuelle Einträge und Korrekturen, bevor Sie die Auswertung oder den PDF-Export verwenden.',
            'Beachten Sie testspezifische Kontrollhinweise, insbesondere bei BRT, LPS-B und BT.',
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
        ],
        beforeTest: ['Weisen Sie die Teilnehmenden darauf hin, dass jede Frage beantwortet werden muss.'],
        duringTest: [
            'Kontrollieren Sie bei Rückfragen, ob eine Antwort ausgewählt wurde.',
            'Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.',
        ],
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
        ],
        beforeTest: ['Weisen Sie die Teilnehmenden darauf hin, dass alle Aufgaben beantwortet werden müssen.'],
        duringTest: ['Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.'],
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
        ],
        beforeTest: ['Weisen Sie die Teilnehmenden darauf hin, dass alle Fragen beantwortet werden müssen.'],
        duringTest: ['Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.'],
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
        ],
        beforeTest: ['Weisen Sie die Teilnehmenden darauf hin, dass alle Aufgaben beantwortet werden müssen.'],
        duringTest: ['Der Test kann bei Bedarf pausiert und anschließend fortgesetzt werden.'],
        afterTest: [],
    },
};

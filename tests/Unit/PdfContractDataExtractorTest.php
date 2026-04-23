<?php

use App\Services\Contracts\PdfContractDataExtractor;

it('extracts expected contract fields from standardized text', function () {
    $extractor = new PdfContractDataExtractor();

    $pdfLike = <<<PDF
stream
BT
(und Frau/Herrn Simone Muster als Teilnehmer) Tj
(Lehrgang OSI - Orientierung, Schulung und Integration) Tj
(Ort Erfurt) Tj
(Dauer Beginn: 02.03.2026 Ende: 01.03.2027) Tj
(Maßnahmezeit 40 Unterrichtseinheiten pro Woche) Tj
(Geburtsdatum: 14.03.1970) Tj
(Straße, Hausnummer: Musterweg 10) Tj
(PLZ, Ort: 99084 Erfurt) Tj
(Tel.: 01234 56789) Tj
(Leistungsträger Jobcenter Erfurt) Tj
ET
endstream
PDF;

    $result = $extractor->extract($pdfLike);

    expect($result['firstname'])->toBe('Simone')
        ->and($result['name'])->toBe('Muster')
        ->and($result['course'])->toBe('OSI - Orientierung, Schulung und Integration')
        ->and($result['location'])->toBe('Erfurt')
        ->and($result['measure_start'])->toBe('2026-03-02')
        ->and($result['measure_end'])->toBe('2027-03-01')
        ->and($result['birthday'])->toBe('1970-03-14')
        ->and($result['zip'])->toBe('99084')
        ->and($result['city_name'])->toBe('Erfurt')
        ->and($result['cost_bearer'])->toBe('Jobcenter Erfurt');
});

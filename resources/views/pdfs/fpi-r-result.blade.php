<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .meta-table { width:100%; border-collapse: collapse; }
        .meta-table td { border: none; padding: 2px 4px; font-size: 12px; }
        table.results { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.results th, table.results td { border: 1px solid #000; padding: 4px; font-size: 10px; }
        table.results th.category, table.results td.category { text-align: left; }
        table.results td.stan { width: 20px; height: 20px; text-align: center; }
        .footer { font-size: 9px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>FPI-R AUSWERTUNGSBOGEN</h1>
    <table class="meta-table">
        <tr>
            <td>Name / Kenn-Nr.:</td><td>{{ $participant_name }}</td>
            <td>Geschlecht:</td><td>{{ $sex }}</td>
            <td>Alter:</td><td>{{ $age }}</td>
        </tr>
        <tr>
            <td>Schulabschluss:</td><td>{{ $education }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Testleiter / Auswerter:</td><td colspan="5"></td>
        </tr>
    </table>
    @if($norm_group)
    <p>Normstichprobe: {{ $norm_group }}</p>
    @endif
    <p>Datum: {{ $date }} | Dauer: {{ $duration }}</p>
    <table class="results">
        <thead>
            <tr>
                <th rowspan="2" class="category">Skala</th>
                <th rowspan="2">Rohwert</th>
                <th colspan="12">Standardwert</th>
                <th rowspan="2">Prozent</th>
                <th rowspan="2">Stanine</th>
            </tr>
            <tr>
                @for($i = 1; $i <= 12; $i++)
                    <th>{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
            <tr>
                <td class="category">{{ $cat['label'] }}</td>
                <td>{{ $cat['raw'] }}</td>
                @for($i = 1; $i <= 12; $i++)
                    <td class="stan">@if($cat['stanine'] == $i) &#9679; @endif</td>
                @endfor
                <td></td>
                <td>{{ $cat['stanine'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="footer">Die Testautoren bitten um Übersendung von Auswertungsbogen für die FPI-Informationsdatenbank bei N<50.</p>
</body>
</html>

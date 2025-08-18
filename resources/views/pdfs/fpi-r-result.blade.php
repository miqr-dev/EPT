<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .meta-table { width:100%; border-collapse: collapse; }
        .meta-table td { border: none; padding: 2px 4px; font-size: 12px; }
        .result-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .result-table th, .result-table td { border: 1px solid #000; padding: 4px; font-size: 9px; }
        .result-table th { text-align: center; }
        .rohwert-col { width: 85px; text-align: center; }
        .standard-col { width: 230px; }
        .graph-col { width: 20px; text-align: center; }
        .right-desc-col { width: 180px; font-size: 9px; }
        .rohwert-box { width:48px; height:20px; border:1px solid #000; border-radius:10px; line-height:20px; font-weight:bold; margin:auto; }
        .graph-dot { width:4px; height:4px; background:#000; border-radius:50%; margin:auto; }
        .section-divider td { border-bottom:1.5px solid #000; }
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
    @php
        function catNum($i) { return $i < 10 ? $i + 1 : ($i === 10 ? 'E' : 'N'); }
    @endphp
    <table class="result-table">
        <thead>
            <tr>
                <th class="rohwert-col">Rohwert</th>
                <th class="standard-col">Standardwert</th>
                <th colspan="9">
                    <div>Normstichprobe</div>
                    <div style="display:flex; justify-content:space-around;">
                        @foreach([4,7,12,17,20,17,12,7,4] as $p)
                            <span>{{ $p }}</span>
                        @endforeach
                    </div>
                    <div style="display:flex; justify-content:space-around;">
                        @foreach([9,8,7,6,5,4,3,2,1] as $s)
                            <span>{{ $s }}</span>
                        @endforeach
                    </div>
                </th>
                <th class="right-desc-col">
                    <div>Prozent</div>
                    <div>Stanine</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $idx => $cat)
            <tr class="{{ in_array($idx, [9,11]) ? 'section-divider' : '' }}">
                <td class="rohwert-col"><div class="rohwert-box">{{ $cat['raw'] }}</div></td>
                <td class="standard-col">
                    <strong>{{ catNum($idx) }}. {{ $cat['label'] }}</strong><br>
                    {!! $cat['commentL'] !!}
                </td>
                @for($s=9; $s>=1; $s--)
                    <td class="graph-col">@if($cat['stanine'] == $s) <div class="graph-dot"></div> @endif</td>
                @endfor
                <td class="right-desc-col">{!! $cat['commentR'] !!}</td>
            </tr>
            @endforeach
            <tr>
                <td class="rohwert-col"><div class="rohwert-box"></div></td>
                <td colspan="11">fehlende Antworten</td>
            </tr>
        </tbody>
    </table>
</body>
</html>


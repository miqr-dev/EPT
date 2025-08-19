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
        .graph-col { width: {{ $grid_width }}px; padding:0; }
        .right-desc-col { width: 180px; font-size: 9px; }
        .rohwert-box { width:48px; height:20px; border:1px solid #000; border-radius:10px; line-height:20px; font-weight:bold; margin:auto; }
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
    <div style="position:relative;">
    <table class="result-table">
        <thead>
            <tr>
                <th class="rohwert-col">Rohwert</th>
                <th class="standard-col">Standardwert</th>
                <th class="graph-col">
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
            <tr class="result-row {{ in_array($idx, [9,11]) ? 'section-divider' : '' }}" style="height:{{ $row_height }}px;">
                <td class="rohwert-col"><div class="rohwert-box">{{ $cat['raw'] }}</div></td>
                <td class="standard-col">
                    <strong>{{ catNum($idx) }}. {{ $cat['label'] }}</strong><br>
                    {!! $cat['commentL'] !!}
                </td>
                @if($idx === 0)
                <td class="graph-col" rowspan="{{ count($categories) }}">
                    <svg width="{{ $grid_width }}" height="{{ $grid_height }}">
                        @for($i=0; $i < count($categories); $i++)
                            @for($j=0; $j < 9; $j++)
                                <circle cx="{{ $j * $cell_width + $cell_width/2 }}" cy="{{ $i * $row_height + $row_height/2 }}" r="2" fill="#000" />
                            @endfor
                        @endfor
                        @foreach([2.5,6.5] as $pos)
                            <line x1="{{ $cell_width * $pos }}" y1="0" x2="{{ $cell_width * $pos }}" y2="{{ $grid_height }}" stroke="#4a90e2" stroke-width="1.5" opacity="0.7" />
                        @endforeach
                        <polyline points="{{ $stanine_points }}" fill="none" stroke="#000" stroke-width="1.5" />
                        <rect x="{{ $cell_width * 3 }}" y="0" width="{{ $cell_width * 3 }}" height="{{ $row_height * 10 }}" fill="none" stroke="#000" stroke-width="1" />
                        <rect x="{{ $cell_width * 3 }}" y="{{ $row_height * 10 }}" width="{{ $cell_width * 3 }}" height="{{ $row_height * 2 }}" fill="none" stroke="#000" stroke-width="1" />
                        <text x="{{ $cell_width * 4.5 }}" y="-5" font-size="11" text-anchor="middle">54%</text>
                        <text x="{{ $cell_width * 4.5 }}" y="{{ $grid_height - 5 }}" font-size="11" text-anchor="middle">54%</text>
                        @foreach([9,11] as $div)
                            <line x1="0" y1="{{ ($div+1) * $row_height }}" x2="{{ $grid_width }}" y2="{{ ($div+1) * $row_height }}" stroke="#000" stroke-width="1.5" />
                        @endforeach
                    </svg>
                </td>
                @endif
                <td class="right-desc-col">{!! $cat['commentR'] !!}</td>
            </tr>
            @endforeach
            <tr>
                <td class="rohwert-col"><div class="rohwert-box"></div></td>
                <td colspan="2">fehlende Antworten</td>
            </tr>
        </tbody>
    </table>
    </div>
</body>
</html>

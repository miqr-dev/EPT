<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .answers-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .answers-table th, .answers-table td { border: 1px solid #000; padding: 2px; text-align: center; }
        .answers-table td.selected { background: #00008b; color: #fff; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .summary-table th, .summary-table td { border: 1px solid #000; padding: 4px; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>

    {{-- Antworttabelle --}}
    <table class="answers-table">
        <thead>
            <tr>
                <th>Nr</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
            </tr>
        </thead>
        <tbody>
            @foreach($answers as $ans)
            <tr>
                <td>{{ $ans['number'] }}</td>
                @foreach(['A','B','C','D'] as $opt)
                    <td class="{{ strtoupper($ans['user_answer']) === $opt ? 'selected' : '' }}">{{ $opt }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rohwert Boxen und Gesamtwerte --}}
    <div style="display:flex; align-items:flex-end; gap:8px; margin-top:20px;">
        <span style="font-weight:bold;">RW</span>
        @foreach($group_scores as $i => $score)
            <div style="display:flex; flex-direction:column; align-items:center;">
                <div style="width:28px;height:28px;border:2px solid #000;display:flex;justify-content:center;align-items:center;font-weight:bold;">{{ $score }}</div>
                <span style="font-size:8px;margin-top:2px;">U{{ $i + 1 }}</span>
            </div>
        @endforeach
        <div style="display:flex; flex-direction:column; align-items:center; margin-left:12px;">
            <div style="width:28px;height:28px;border:2px solid #000;background:#e0e7ff;display:flex;justify-content:center;align-items:center;font-weight:bold;">{{ $total_score }}</div>
            <span style="font-size:8px;margin-top:2px;font-weight:bold;">RW GS</span>
        </div>
        <div style="display:flex; flex-direction:column; align-items:center; margin-left:12px;">
            <div style="width:28px;height:28px;border:2px solid #000;background:#fef08a;display:flex;justify-content:center;align-items:center;font-weight:bold;">{{ $prozentrang }}</div>
            <span style="font-size:8px;margin-top:2px;font-weight:bold;">PR</span>
        </div>
    </div>

    {{-- SN Werte Chart --}}
    @php
        $rows = count($group_stanines);
        $cellW = 30; $cellH = 30;
        $chartW = $cellW * 9; $chartH = $cellH * $rows;
        $points = [];
        foreach($group_stanines as $idx => $sn){
            $x = ($sn - 0.5) * $cellW;
            $y = $cellH * $idx + $cellH / 2;
            $points[] = $x . ',' . $y;
        }
    @endphp
    <svg width="{{ $chartW + 40 }}" height="{{ $chartH + 20 }}" style="margin-top:25px;">
        <g transform="translate(0,0)">
            <rect x="{{ $cellW * 3 }}" y="0" width="{{ $cellW * 3 }}" height="{{ $chartH }}" fill="rgba(144,238,144,0.3)" />
            @for($i=0;$i<=9;$i++)
                <line x1="{{ $cellW*$i }}" y1="0" x2="{{ $cellW*$i }}" y2="{{ $chartH }}" stroke="#ccc" />
            @endfor
            @for($i=0;$i<=$rows;$i++)
                <line x1="0" y1="{{ $cellH*$i }}" x2="{{ $chartW }}" y2="{{ $cellH*$i }}" stroke="#ccc" />
            @endfor
            <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#1d4ed8" stroke-width="2" />
            @foreach($points as $pt)
                @php [$px, $py] = explode(',', $pt); @endphp
                <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="#1d4ed8" />
            @endforeach
            @for($i=1;$i<=9;$i++)
                <text x="{{ ($i-0.5)*$cellW }}" y="{{ $chartH + 12 }}" font-size="10" text-anchor="middle">{{ $i }}</text>
            @endfor
            @for($i=0;$i<$rows;$i++)
                <text x="{{ $chartW + 5 }}" y="{{ $cellH*$i + $cellH/2 }}" font-size="10" dominant-baseline="middle">U{{ $i+1 }}</text>
            @endfor
        </g>
    </svg>

    {{-- Tabelle der Gruppenwerte und Stanine --}}
    <table class="summary-table">
        <thead>
            <tr>
                <th>Gruppe</th>
                <th>RW</th>
                <th>SN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group_scores as $i => $score)
            <tr>
                <td>U{{ $i + 1 }}</td>
                <td>{{ $score }}</td>
                <td>{{ $group_stanines[$i] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:8px;">RW Gesamt: {{ $total_score }} | PR: {{ $prozentrang }}</p>
</body>
</html>


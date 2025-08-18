<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $test_name }}</h1>
    <p>Datum: {{ $date }}</p>
    <p>Teilnehmer: {{ $participant_name }}</p>
    <p>Dauer: {{ $duration }}</p>
    <table>
        <thead>
            <tr>
                <th>Kategorie</th>
                <th>Rohwert</th>
                <th>Stanine</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
            <tr>
                <td>{{ $cat['label'] }}</td>
                <td>{{ $cat['raw'] }}</td>
                <td>{{ $cat['stanine'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

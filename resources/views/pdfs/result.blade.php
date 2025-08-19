<!DOCTYPE html>
<html>
<head>
    <title>Test Result</title>
    <style>
        body { font-family: sans-serif; }
        .container { margin: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .content { margin-top: 20px; }
        .json-result { white-space: pre-wrap; background-color: #f4f4f4; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Test Result</h1>
        </div>
        <p><strong>Participant:</strong> {{ $testResult->assignment->participant->name }}</p>
        <p><strong>Test:</strong> {{ $testResult->assignment->test->name }}</p>
        <div class="content">
            <h2>Test Data</h2>
            <pre class="json-result">{{ json_encode($testResult->result_json, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</body>
</html>

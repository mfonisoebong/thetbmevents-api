<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Monitor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            color: #1f2937;
            background: #f9fafb;
        }
        h1 {
            margin-bottom: 0.4rem;
        }
        .muted {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .error {
            color: #b91c1c;
            margin-bottom: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 0.6rem;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        code {
            background: #f3f4f6;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<h1>Request Monitor</h1>
<p class="muted">Redis key: <code>{{ $redisKey }}</code></p>

@if($error)
    <p class="error">{{ $error }}</p>
@endif

@if($logs->isEmpty())
    <p>No request logs yet.</p>
@else
    <table>
        <thead>
        <tr>
            <th>Timestamp</th>
            <th>HTTP Verb</th>
            <th>Route</th>
            <th>Parameters</th>
            <th>Duration (ms)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log['timestamp'] ?? '-' }}</td>
                <td>{{ $log['method'] ?? '-' }}</td>
                <td>{{ $log['route'] ?? '-' }}</td>
                <td>{{ $log['parameters'] ?? '-' }}</td>
                <td>{{ $log['duration_ms'] ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>


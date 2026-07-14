<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Empty Week Calendar</title>
    <style>
        :root {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --line: #d7dde4;
            --text: #1b2430;
            --muted: #5f6b7a;
            --accent: #1768ac;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #edf4fb 0%, var(--bg) 240px);
            color: var(--text);
        }

        .container {
            max-width: 1200px;
            margin: 24px auto;
            padding: 0 16px 24px;
        }

        .header {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        h1 {
            margin: 0;
            font-size: 1.4rem;
        }

        .meta {
            margin-top: 6px;
            color: var(--muted);
        }

        .nav {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: 1px solid var(--accent);
            background: var(--accent);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn.alt {
            background: var(--surface);
            color: var(--accent);
            border-color: var(--line);
        }

        .calendar {
            margin-top: 16px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            border-bottom: 1px solid var(--line);
            border-right: 1px solid var(--line);
            padding: 12px;
            vertical-align: top;
        }

        th:last-child,
        td:last-child {
            border-right: none;
        }

        thead th {
            background: #f8fbff;
            text-align: left;
        }

        .empty-cell {
            height: 120px;
            background: repeating-linear-gradient(
                -45deg,
                #ffffff,
                #ffffff 10px,
                #f7fafc 10px,
                #f7fafc 20px
            );
        }

        .note {
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        @media (max-width: 900px) {
            h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Empty Week Calendar</h1>
            <div class="meta">Week {{ $week }} ({{ $year }})</div>
            <div class="nav">
                <a class="btn alt" href="{{ route('calendar.week', ['token' => $token, 'week' => $previousWeek->format('W'), 'year' => $previousWeek->format('o')]) }}">Previous</a>
                <a class="btn" href="{{ route('calendar.week', ['token' => $token, 'week' => now()->format('W'), 'year' => now()->format('o')]) }}">Current week</a>
                <a class="btn alt" href="{{ route('calendar.week', ['token' => $token, 'week' => $nextWeek->format('W'), 'year' => $nextWeek->format('o')]) }}">Next</a>
            </div>
        </div>

        <div class="calendar">
            <table>
                <thead>
                    <tr>
                        @foreach ($days as $day)
                            <th>{{ $day['title'] }}<br>{{ $day['subtitle'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for ($i = 0; $i < 7; $i++)
                            <td class="empty-cell"></td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="note">This page is intentionally empty and protected only by the URL token.</div>
    </div>
</body>
</html>

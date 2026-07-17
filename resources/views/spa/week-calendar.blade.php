<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Flexworker Week Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f0f6f0;
            --surface: #ffffff;
            --line: #d5e1d2;
            --text: #162314;
            --muted: #536451;
            --accent: #1f8f4e;
            --accent-dark: #106537;
            --match: #fff3bf;
            --driver: #d8f6e1;
            --shadow: 0 12px 30px rgba(13, 44, 22, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(circle at 8% 12%, rgba(31, 143, 78, 0.12) 0, transparent 34%),
                radial-gradient(circle at 92% 18%, rgba(16, 101, 55, 0.11) 0, transparent 28%),
                linear-gradient(180deg, #e8f4e9 0%, var(--bg) 300px);
            color: var(--text);
        }

        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 16px 16px 24px;
        }

        .header {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: var(--shadow);
        }

        h1 {
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: 0.2px;
        }

        .meta {
            margin-top: 6px;
            color: var(--muted);
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: 1px solid var(--accent);
            background: var(--accent);
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: transform .15s ease, background-color .15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            background: var(--accent-dark);
        }

        .btn.alt {
            background: var(--surface);
            color: var(--accent);
            border-color: var(--line);
        }

        .toolbar {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .search {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search input {
            display: none;
        }

        .search select {
            min-width: 280px;
            max-width: 420px;
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 0.95rem;
            background: #fff;
        }

        .search .select2-container {
            min-width: 280px;
            max-width: 420px;
            width: 100% !important;
        }

        .search .select2-container .select2-selection--single {
            height: 40px;
            border: 1px solid var(--line);
            border-radius: 10px;
            display: flex;
            align-items: center;
        }

        .search .select2-container .select2-selection__rendered {
            line-height: 38px;
        }

        .search .select2-container .select2-selection__arrow {
            height: 38px;
        }

        .search-meta {
            color: var(--muted);
            font-size: .9rem;
        }

        .companies {
            margin-top: 18px;
            display: grid;
            gap: 16px;
        }

        .company-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .company-card .grid {
            margin-top: 0;
            padding: 10px;
        }

        .company-head {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(90deg, #edf9f1 0%, #f7fcf8 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .company-title {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .company-count {
            color: var(--muted);
            font-size: .85rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            padding: 3px 8px;
        }

        .grid {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
            overflow: visible;
            padding-bottom: 0;
        }

        .day {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.93);
            min-height: 280px;
            box-shadow: var(--shadow);
            animation: fadein .35s ease both;
        }

        .day-head {
            border-bottom: 1px solid var(--line);
            padding: 10px 12px;
            background: #f6fbf6;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        .day-title {
            font-weight: 700;
            font-size: .95rem;
        }

        .day-sub {
            color: var(--muted);
            font-size: .84rem;
        }

        .day-body {
            padding: 10px;
            display: grid;
            gap: 9px;
        }

        .shift {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            padding: 10px;
        }

        .shift-time {
            font-weight: 700;
            color: var(--accent-dark);
            font-size: .94rem;
        }

        .shift-company {
            margin-top: 5px;
            font-weight: 600;
            line-height: 1.35;
            font-size: .92rem;
        }

        .shift-meta {
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.4;
            margin-top: 4px;
        }

        .workers {
            margin-top: 8px;
            display: grid;
            gap: 6px;
        }

        .worker {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 6px 8px;
            background: #fbfdfb;
            font-size: .84rem;
            line-height: 1.3;
        }

        .worker.match {
            background: var(--match);
            border-color: #e6cc61;
        }

        .worker.driver {
            background: var(--driver);
            border-color: #8ed5a8;
        }

        .driver-line {
            margin-top: 4px;
            font-size: .76rem;
            color: #2f4c2b;
        }

        .empty {
            color: var(--muted);
            font-size: .86rem;
            padding: 8px;
            border: 1px dashed var(--line);
            border-radius: 9px;
            background: #f8fbf8;
        }

        .note {
            margin-top: 12px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        @keyframes fadein {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            h1 { font-size: 1.2rem; }
            .search .select2-container { min-width: 220px; }
            .grid {
                grid-template-columns: 1fr;
                overflow: visible;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Flexworker Week Planner</h1>
            <div class="meta">Week {{ $week }} ({{ $year }})</div>

            <div class="toolbar">
                <form class="search" method="GET" action="{{ route('calendar.week') }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <select name="q" id="worker-search-select">
                        <option value="">Search flexworker name or registration number</option>
                        @foreach ($workerOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($search === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn">Search</button>
                    @if ($search !== '')
                        <a class="btn alt" href="{{ route('calendar.week', ['token' => $token]) }}">Clear</a>
                    @endif
                </form>

                <div class="search-meta">
                    @if ($import)
                        Last updated {{ $import->imported_at?->format('d-m-Y H:i') }} | {{ $resultShiftCount }} visible shifts in {{ $resultCompanyCount }} companies
                    @else
                        Could not import
                    @endif
                </div>
            </div>
        </div>

        @if (count($companies) === 0)
            <div class="note">
                @if ($search !== '')
                    No shifts found for this search in week {{ $week }}.
                @else
                    No shifts available in week {{ $week }}.
                @endif
            </div>
        @else
            <div class="companies">
                @foreach ($companies as $company)
                    <section class="company-card">
                        <header class="company-head">
                            <div class="company-title">{{ $company['company_name'] }}</div>
                            <div class="company-count">{{ $company['shift_count'] }} shifts</div>
                        </header>

                        <div class="grid">
                            @foreach ($company['days'] as $day)
                                <section class="day">
                                    <header class="day-head">
                                        <div class="day-title">{{ $day['title'] }}</div>
                                        <div class="day-sub">{{ $day['subtitle'] }}</div>
                                    </header>
                                    <div class="day-body">
                                        @forelse ($day['shifts'] as $shift)
                                            <article class="shift">
                                                <div class="shift-time">{{ $shift['time_label'] }}</div>
                                                <div class="shift-meta">
                                                    @if ($shift['subsidiary_name'])
                                                        {{ $shift['subsidiary_name'] }}<br>
                                                    @endif
                                                    @if ($shift['role_name'])
                                                        {{ $shift['role_name'] }}
                                                    @endif
                                                    @if ($shift['cost_center_name'])
                                                        | {{ $shift['cost_center_name'] }}
                                                    @endif
                                                    @if ($shift['work_address'])
                                                        <br>{{ $shift['work_address'] }}
                                                    @endif
                                                </div>

                                                <div class="workers">
                                                    @forelse ($shift['assignments'] as $assignment)
                                                        <div @class([
                                                            'worker',
                                                            'match' => $assignment['matches_search'],
                                                            'driver' => $assignment['is_driver'],
                                                        ])>
                                                            <strong>{{ $assignment['worker_name'] }}</strong>
                                                            @if ($assignment['registration'])
                                                                <div>#{{ $assignment['registration'] }}</div>
                                                            @endif
                                                            <div class="driver-line">
                                                                @if ($assignment['is_driver'])
                                                                    This flexworker is the chauffeur for this shift.
                                                                @elseif ($shift['driver_name'])
                                                                    Driver for this flexworker: <strong>{{ $shift['driver_name'] }}</strong>
                                                                @else
                                                                    No chauffeur assigned yet.
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="empty">No flexworkers assigned yet.</div>
                                                    @endforelse
                                                </div>
                                            </article>
                                        @empty
                                            <div class="empty">
                                                @if ($search !== '')
                                                    No shifts found for this search on {{ $day['title'] }}.
                                                @else
                                                    No shifts scheduled.
                                                @endif
                                            </div>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        <div class="note">Access stays protected by the URL token. Drivers are marked inside each shift assignment list.</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            var $select = window.jQuery ? window.jQuery('#worker-search-select') : null;
            if (! $select || $select.length === 0 || ! window.jQuery.fn.select2) {
                return;
            }

            $select.select2({
                placeholder: 'Search flexworker name or registration number',
                allowClear: true,
                width: 'resolve'
            });
        })();
    </script>
</body>
</html>

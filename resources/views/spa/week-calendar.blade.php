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

        .week-nav {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .week-nav .week-label {
            font-weight: 700;
            font-size: 1rem;
            min-width: 140px;
            text-align: center;
        }

        .week-nav .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .week-content-wrap {
            position: relative;
        }

        .week-loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.6);
            border-radius: 16px;
            z-index: 10;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--muted);
        }

        .week-loading-overlay.active {
            display: flex;
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

            <div class="week-nav">
                <button id="btn-prev-week" class="btn alt">&#8592; Previous week</button>
                <span class="week-label" id="week-label">Week {{ $week }} ({{ $year }})</span>
                <button id="btn-next-week" class="btn alt" {{ $isCurrentWeek ? 'disabled' : '' }}>Next week &#8594;</button>
            </div>

            <div class="toolbar">
                <form class="search" method="GET" action="{{ route('calendar.week') }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="week" value="{{ $week }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <select name="q" id="worker-search-select">
                        <option value="">Search flexworker name or registration number</option>
                        @foreach ($workerOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($search === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn">Search</button>
                    @if ($search !== '')
                        <a class="btn alt" href="{{ route('calendar.week', ['token' => $token, 'week' => $week, 'year' => $year]) }}">Clear</a>
                    @endif
                </form>

                <div class="search-meta" id="search-meta">
                    @if ($import)
                        Last updated {{ $import->imported_at?->format('d-m-Y H:i') }} | {{ $resultShiftCount }} visible shifts in {{ $resultCompanyCount }} companies
                    @else
                        No data available for this week
                    @endif
                </div>
            </div>
        </div>

        <div class="week-content-wrap">
            <div class="week-loading-overlay" id="week-loading">Loading&hellip;</div>
            <div id="week-body">
                @include('spa.partials.week-body')
            </div>
        </div>

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
    <script>
        (function () {
            var token = {{ Js::from($token) }};
            var search = {{ Js::from($search) }};
            var displayWeek = {{ $week }};
            var displayYear = {{ $year }};
            var isCurrentWeek = {{ $isCurrentWeek ? 'true' : 'false' }};

            var btnPrev = document.getElementById('btn-prev-week');
            var btnNext = document.getElementById('btn-next-week');
            var weekLabel = document.getElementById('week-label');
            var weekBody = document.getElementById('week-body');
            var weekLoading = document.getElementById('week-loading');
            var searchMeta = document.getElementById('search-meta');

            function isoWeekBounds(year, week) {
                // Returns { prevWeek, prevYear, nextWeek, nextYear }
                var jan4 = new Date(year, 0, 4);
                var dayOfWeek = jan4.getDay() || 7;
                var weekStart = new Date(jan4);
                weekStart.setDate(jan4.getDate() - (dayOfWeek - 1) + (week - 1) * 7);

                var prevDate = new Date(weekStart);
                prevDate.setDate(weekStart.getDate() - 7);
                var nextDate = new Date(weekStart);
                nextDate.setDate(weekStart.getDate() + 7);

                return {
                    prevWeek: isoWeekNum(prevDate),
                    prevYear: isoYear(prevDate),
                    nextWeek: isoWeekNum(nextDate),
                    nextYear: isoYear(nextDate),
                };
            }

            function isoWeekNum(d) {
                var date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
                date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
                var yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
                return Math.ceil(((date - yearStart) / 86400000 + 1) / 7);
            }

            function isoYear(d) {
                var date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
                date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
                return date.getUTCFullYear();
            }

            function updateNavButtons() {
                btnNext.disabled = isCurrentWeek;
            }

            function buildPageUrl(week, year) {
                var params = new URLSearchParams(window.location.search);
                params.set('week', week);
                params.set('year', year);
                return window.location.pathname + '?' + params.toString();
            }

            function loadWeek(week, year, pushHistory) {
                var params = new URLSearchParams({
                    week: week,
                    year: year,
                    q: search,
                });
                if (token) params.set('token', token);

                weekLoading.classList.add('active');
                btnPrev.disabled = true;
                btnNext.disabled = true;

                fetch('/week-data?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    displayWeek = data.week;
                    displayYear = data.year;
                    isCurrentWeek = data.isCurrentWeek;

                    weekLabel.textContent = 'Week ' + data.week + ' (' + data.year + ')';
                    weekBody.innerHTML = data.html;

                    // Keep search form in sync with the displayed week.
                    var inputWeek = document.querySelector('input[name="week"]');
                    var inputYear = document.querySelector('input[name="year"]');
                    if (inputWeek) inputWeek.value = data.week;
                    if (inputYear) inputYear.value = data.year;

                    if (pushHistory !== false) {
                        history.pushState({ week: data.week, year: data.year }, '', buildPageUrl(data.week, data.year));
                    }

                    if (data.import) {
                        searchMeta.textContent = 'Last updated ' + data.import.imported_at +
                            ' | ' + data.import.shift_count + ' visible shifts in ' +
                            data.import.company_count + ' companies';
                    } else {
                        searchMeta.textContent = 'No data available for this week';
                    }
                })
                .catch(function () {
                    weekBody.innerHTML = '<div class="note">Failed to load week data. Please try again.</div>';
                })
                .finally(function () {
                    weekLoading.classList.remove('active');
                    btnPrev.disabled = false;
                    updateNavButtons();
                });
            }

            btnPrev.addEventListener('click', function () {
                var bounds = isoWeekBounds(displayYear, displayWeek);
                loadWeek(bounds.prevWeek, bounds.prevYear);
            });

            btnNext.addEventListener('click', function () {
                var bounds = isoWeekBounds(displayYear, displayWeek);
                loadWeek(bounds.nextWeek, bounds.nextYear);
            });

            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.week && e.state.year) {
                    loadWeek(e.state.week, e.state.year, false);
                }
            });

            // Seed the initial history entry so popstate fires correctly when navigating back.
            history.replaceState({ week: displayWeek, year: displayYear }, '', window.location.href);

            updateNavButtons();
        })();
    </script>
</body>
</html>

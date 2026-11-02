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
                    @if (in_array($accessScope ?? '', ['admin', 'view_all'], true) && isset($companyTokens[$company['company_name']]))
                        <div class="company-actions">
                            <button
                                type="button"
                                class="btn alt small btn-copy-company-url"
                                data-url="{{ $companyTokens[$company['company_name']]['url'] }}"
                            >Copy company link</button>
                            @if ($accessScope === 'admin')
                                <button
                                    type="button"
                                    class="btn alt small btn-renew-company-token"
                                    data-company-id="{{ $companyTokens[$company['company_name']]['id'] }}"
                                >Renew token</button>
                            @endif
                        </div>
                    @endif
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
                                            @if ($shift['role_name'])
                                                {{ $shift['role_name'] }}
                                            @endif
                                            @if ($shift['cost_center_name'])
                                                | {{ $shift['cost_center_name'] }}
                                            @endif
                                            @if ($shift['work_address'])
                                                <br>{{ $shift['work_address'] }}
                                            @endif
                                            @if (
                                                $shift['function_name']
                                                && mb_strtolower(trim((string) $shift['function_name'])) !== mb_strtolower(trim((string) $shift['role_name']))
                                            )
                                                <br>{{ $shift['function_name'] }}
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
                                                    @foreach ($assignment['status_labels'] as $status)
                                                        @php
                                                            $statusColor = match (mb_strtolower(rtrim(trim($status), '.'))) {
                                                                'stop' => 'red',
                                                                'vrij', 'vakantie' => 'yellow',
                                                                'no show' => 'purple',
                                                                default => 'blue',
                                                            };
                                                        @endphp
                                                        <div class="worker-status worker-status-{{ $statusColor }}">{{ $status }}</div>
                                                    @endforeach
                                                    @if ($assignment['has_different_schedule'])
                                                        <div class="worker-time">{{ $assignment['time_label'] }}</div>
                                                    @endif
                                                    @if ($assignment['registration'])
                                                        <div>#{{ $assignment['registration'] }}</div>
                                                    @endif
                                                    <div class="driver-line">
                                                        @if ($assignment['is_driver'])
                                                            @if (count($shift['driver_names']) > 1)
                                                                Is one of the chauffeurs for this shift.
                                                            @else
                                                                Is the chauffeur for this shift.
                                                            @endif
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

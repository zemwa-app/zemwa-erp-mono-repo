@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@section('filter-section')
    @if (($enabledMonitorSeatCount ?? 0) > 0)
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.menu.teams')</p>
            <div class="select-status">
                <select name="department" id="department" class="form-control select-picker" data-live-search="true" data-size="8" data-container="body">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('monitor::app.status')</p>
            <div class="select-status">
                <select name="status" id="monitor-status-filter" class="form-control select-picker" data-size="8" data-container="body">
                    <option value="all">@lang('app.all')</option>
                    <option value="online">@lang('monitor::app.onlineNow')</option>
                    <option value="idle">@lang('monitor::app.idle')</option>
                    <option value="paused">@lang('monitor::app.paused')</option>
                    <option value="offline">@lang('monitor::app.offline')</option>
                </select>
            </div>
        </div>

        @include('monitor::partials.filter-search', [
            'id' => 'monitor-dashboard-search',
            'name' => 'search',
            'placeholder' => __('monitor::app.searchEmployeeOrApp'),
        ])

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
    @endif
@endsection

@section('content')
    @if (!($hasInstallers ?? false))
        <div class="content-wrapper">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card bg-white border-0 b-shadow-4 p-20 text-center">
                        <span class="d-flex align-items-center justify-content-center mb-4 rounded"
                            style="width: 56px; height: 56px; background-color: #eef2ff; color: #4f46e5;">
                            <i class="fa fa-download f-21" aria-hidden="true"></i>
                        </span>
                        <h3 class="f-16 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.noInstallersTitle')</h3>
                        <p class="f-14 text-dark-grey mb-4">
                            @lang('monitor::app.noInstallersMessage')
                        </p>
                        <div class="d-flex flex-wrap align-items-center justify-content-center">
                            @if ($canManageInstallers ?? false)
                                <a href="{{ route('monitor.installer-settings.index') }}"
                                   class="btn btn-primary mr-2 mb-2">
                                    <i class="fa fa-upload mr-1" aria-hidden="true"></i>
                                    @lang('monitor::app.noInstallersCta')
                                </a>
                            @else
                                <p class="f-14 text-lightest mb-0">@lang('monitor::app.noInstallersContactAdmin')</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (($enabledMonitorSeatCount ?? 0) === 0)
        <div class="content-wrapper">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card bg-white border-0 b-shadow-4 p-20 text-center">
                        <span class="d-flex align-items-center justify-content-center mb-4 rounded"
                            style="width: 56px; height: 56px; background-color: #eef2ff; color: #4f46e5;">
                            <i class="fa fa-desktop f-21" aria-hidden="true"></i>
                        </span>
                        <h3 class="f-16 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.noMonitorEmployeesTitle')</h3>
                        <p class="f-14 text-dark-grey mb-4">
                            @lang('monitor::app.noMonitorEmployeesMessage')
                        </p>
                        <div class="d-flex flex-wrap align-items-center justify-content-center">
                            @if (user()->permission('view_monitor') == 'all')
                                <a href="{{ route('monitor.config.index') }}"
                                   class="btn btn-primary mr-2 mb-2">
                                    <i class="fa fa-cog mr-1" aria-hidden="true"></i>
                                    @lang('monitor::app.noMonitorEmployeesCta')
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
    @php
        $dashboardHtml = $dashboard['html'] ?? [];
    @endphp

    <div class="content-wrapper">
        <x-cards.data class="mb-3" title="Team Health Overview">
            <x-slot name="action">
                <div class="d-flex align-items-center f-12 text-lightest">
                    <span class="badge badge-success mr-2">Live</span>
                    <span>@lang('monitor::app.lastUpdated'): <span id="monitor-last-updated" class="f-w-500 text-darkest-grey"></span></span>
                </div>
            </x-slot>
            <p class="f-12 text-lightest mb-3">A fast read on whether the team is okay today.</p>
            <div id="monitor-team-health-overview">
                {!! $dashboardHtml['team_health'] ?? view('monitor::dashboard.partials.team-health-overview', ['dashboard' => $dashboard])->render() !!}
            </div>
        </x-cards.data>

        <div class="mb-3" id="monitor-attention-required">
            {!! $dashboardHtml['attention_required'] ?? view('monitor::dashboard.partials.attention-required', ['dashboard' => $dashboard])->render() !!}
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-12 mb-3">
                @include('monitor::dashboard.partials.team-live-status-table', ['employees' => $employees])
            </div>
            <div class="col-xl-4 col-lg-12 mb-3">
                <div id="monitor-workforce-snapshot">
                    {!! $dashboardHtml['workforce_snapshot'] ?? view('monitor::dashboard.partials.workforce-snapshot', ['dashboard' => $dashboard])->render() !!}
                </div>
            </div>
        </div>

        <div id="monitor-workforce-analytics">
            {!! $dashboardHtml['workforce_analytics'] ?? view('monitor::dashboard.partials.workforce-analytics', ['dashboard' => $dashboard])->render() !!}
        </div>
    </div>
    @endif
@endsection

@if (($enabledMonitorSeatCount ?? 0) > 0)
@push('scripts')
    <script>
        (function () {
            const liveStatusUrl = "{{ url('/api/tracker/live-status') }}";
            const employeeShowUrlTemplate = "{{ route('monitor.show', ':id') }}";
            const refreshIntervalMs = 30000;

            const statusLabels = {
                online: "@lang('monitor::app.onlineNow')",
                idle: "@lang('monitor::app.idle')",
                paused: "@lang('monitor::app.paused')",
                offline: "@lang('monitor::app.offline')",
            };

            const statusPillClass = {
                online: 'badge badge-success',
                idle: 'badge badge-warning',
                paused: 'badge badge-warning',
                offline: 'badge badge-secondary',
            };

            const statusDotColor = {
                online: '#22c55e',
                idle: '#f97316',
                paused: '#eab308',
                offline: '#6b7280',
            };

            const scoreBarClass = {
                green: 'progress-bar-success',
                yellow: 'progress-bar-warning',
                orange: 'progress-bar-warning',
                red: 'progress-bar-danger',
                gray: 'progress-bar-secondary',
            };

            const scoreTextClass = (score) => {
                if (score >= 80) {
                    return 'text-success';
                }
                if (score >= 60) {
                    return 'text-warning';
                }
                if (score >= 40) {
                    return 'text-warning';
                }
                return 'text-danger';
            };

            const scoreTone = (score) => {
                if (score >= 80) {
                    return 'green';
                }
                if (score >= 60) {
                    return 'yellow';
                }
                if (score >= 40) {
                    return 'orange';
                }
                return 'red';
            };

            const escapeHtml = (value) => {
                if (value === null || value === undefined) {
                    return '';
                }
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            };

            const buildStatusBadge = (status) => {
                const label = statusLabels[status] || status;
                return `<span class="${statusPillClass[status] || statusPillClass.offline}">${escapeHtml(label)}</span>`;
            };

            const buildAppIcon = (app) => {
                const letter = escapeHtml(app?.letter || '?');
                const color = app?.color || '#64748b';

                return `
                    <span class="d-flex align-items-center justify-content-center rounded border mr-2"
                        style="width: 28px; height: 28px; background-color: ${color}; color: #fff; font-size: 11px; font-weight: 600;">
                        ${letter}
                    </span>
                `;
            };

            const buildScoreBar = (score) => {
                const tone = scoreTone(score);
                return `
                    <div class="w-100" style="min-width: 120px;">
                        <div class="progress" style="height: 8px; margin-bottom: 0;">
                            <div class="progress-bar ${scoreBarClass[tone]}" role="progressbar" style="width: ${Math.max(0, Math.min(100, score))}%"></div>
                        </div>
                        <div class="text-right mt-1">
                            <span class="f-11 font-weight-bold ${scoreTextClass(score)}">${score.toFixed(1)}%</span>
                        </div>
                    </div>
                `;
            };

            const updateDashboardFragments = (dashboard) => {
                if (!dashboard || !dashboard.html) {
                    return;
                }

                $('#monitor-team-health-overview').html(dashboard.html.team_health || '');
                $('#monitor-attention-required').html(dashboard.html.attention_required || '');
                $('#monitor-workforce-snapshot').html(dashboard.html.workforce_snapshot || '');
                $('#monitor-workforce-analytics').html(dashboard.html.workforce_analytics || '');
            };

            const filterEmployees = (employees) => {
                const q = ($('#monitor-dashboard-search').val() || '').toLowerCase().trim();
                const status = $('#monitor-status-filter').val() || 'all';

                return (employees || []).filter((employee) => {
                    if (status !== 'all' && (employee.status || 'offline') !== status) {
                        return false;
                    }
                    if (!q) {
                        return true;
                    }
                    const haystack = [
                        employee.name,
                        employee.employee_code,
                        employee.department,
                        employee.active_app,
                    ].join(' ').toLowerCase();

                    return haystack.includes(q);
                });
            };

            const renderEmployees = (employees) => {
                const filtered = filterEmployees(employees);

                if (!filtered || filtered.length === 0) {
                    $('#monitor-employees-body').html(
                        '<tr><td colspan="6" class="text-center f-14 text-lightest p-4">@lang('messages.noRecordFound')</td></tr>'
                    );
                    return;
                }

                const rows = filtered.map((employee) => {
                    const status = employee.status || 'offline';
                    const score = Number(employee.score ?? 0);
                    const activeApp = employee.active_app ? escapeHtml(employee.active_app) : '—';
                    const avatarUrl = employee.avatar_url ? escapeHtml(employee.avatar_url) : '';
                    const activeAppIcon = buildAppIcon(employee.active_app_icon || {});
                    const viewUrl = employeeShowUrlTemplate.replace(':id', employee.user_id);
                    const dotColor = statusDotColor[status] || statusDotColor.offline;
                    const departmentBadge = employee.department
                        ? `<span class="badge badge-secondary f-11 mt-1">${escapeHtml(employee.department)}</span>`
                        : '';

                    return `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="position-relative mr-3">
                                        ${avatarUrl
                                            ? `<img src="${avatarUrl}" alt="${escapeHtml(employee.name)}" class="taskEmployeeImg rounded" style="width: 40px; height: 40px;">`
                                            : `<div class="taskEmployeeImg rounded d-flex align-items-center justify-content-center bg-additional-grey f-14 font-weight-bold text-dark-grey" style="width: 40px; height: 40px;">${escapeHtml((employee.name || '?').slice(0, 1).toUpperCase())}</div>`
                                        }
                                        <span class="position-absolute rounded-circle border border-white"
                                            style="bottom: 0; right: 0; width: 12px; height: 12px; background-color: ${dotColor};"></span>
                                    </div>
                                    <div>
                                        <p class="mb-0 f-14 f-w-500 text-darkest-grey text-truncate">${escapeHtml(employee.name)}</p>
                                        <p class="mb-0 f-12 text-lightest">${escapeHtml(employee.employee_code)}</p>
                                        ${departmentBadge}
                                    </div>
                                </div>
                            </td>
                            <td>${buildStatusBadge(status)}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    ${activeAppIcon}
                                    <div>
                                        <p class="mb-0 f-14 text-darkest-grey text-truncate">${activeApp}</p>
                                        <p class="mb-0 f-11 text-lightest">${escapeHtml(employee.last_activity_label || 'No recent activity')}</p>
                                    </div>
                                </div>
                            </td>
                            <td>${buildScoreBar(score)}</td>
                            <td class="f-14 text-dark-grey">${escapeHtml(employee.last_activity_label || 'No recent activity')}</td>
                            <td class="text-right">
                                <a href="${viewUrl}" class="btn btn-primary btn-sm">@lang('app.view')</a>
                            </td>
                        </tr>
                    `;
                }).join('');

                $('#monitor-employees-body').html(rows);
            };

            const setLastUpdated = () => {
                const now = new Date();
                $('#monitor-last-updated').text(now.toLocaleTimeString());
            };

            const refreshLiveStatus = () => {
                const department = $('#department').val() || 'all';

                $.ajax({
                    url: liveStatusUrl,
                    type: 'GET',
                    data: { department: department },
                    success: function (response) {
                        const data = response;
                        lastEmployees = data.employees || [];
                        updateDashboardFragments(data.dashboard || {});
                        applyLocalFilters();
                        setLastUpdated();
                    },
                });
            };

            if (typeof refreshSelectPicker === 'function') {
                refreshSelectPicker('.filter-box .select-picker');
            }

            let lastEmployees = @json($employees);

            const updateResetVisibility = () => {
                const hasFilters = ($('#department').val() || 'all') !== 'all'
                    || ($('#monitor-status-filter').val() || 'all') !== 'all'
                    || ($('#monitor-dashboard-search').val() || '').trim() !== '';
                $('#reset-filters').toggleClass('d-none', !hasFilters);
            };

            const applyLocalFilters = () => {
                renderEmployees(lastEmployees);
                updateResetVisibility();
            };

            $('body').off('change.monitorDashboard', '#department')
                .on('change.monitorDashboard', '#department', function () {
                    refreshLiveStatus();
                });

            $('body').off('change.monitorDashboard input.monitorDashboard', '#monitor-status-filter, #monitor-dashboard-search')
                .on('change.monitorDashboard input.monitorDashboard', '#monitor-status-filter, #monitor-dashboard-search', function () {
                    applyLocalFilters();
                });

            $('body').off('click.monitorDashboard', '#reset-filters')
                .on('click.monitorDashboard', '#reset-filters', function () {
                    $('#department').val('all');
                    $('#monitor-status-filter').val('all');
                    $('#monitor-dashboard-search').val('');
                    if (typeof refreshSelectPicker === 'function') {
                        refreshSelectPicker('.filter-box .select-picker');
                    }
                    refreshLiveStatus();
                });

            $('body').off('click.monitorDashboard', '.monitor-copy-employee-id')
                .on('click.monitorDashboard', '.monitor-copy-employee-id', function () {
                    const value = $(this).data('copyValue');
                    if (!value || !navigator.clipboard) {
                        return;
                    }

                    navigator.clipboard.writeText(value);
                });

            setLastUpdated();
            applyLocalFilters();
            setInterval(refreshLiveStatus, refreshIntervalMs);
        })();
    </script>
@endpush
@endif

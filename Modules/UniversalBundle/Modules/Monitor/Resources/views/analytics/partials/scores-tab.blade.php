<div class="p-20">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3">
        <p class="mb-2 mb-sm-0 f-14 text-dark-grey">
            @lang('monitor::app.scoresListMeta', ['shown' => $shown_count, 'total' => $total_employees, 'online' => $online_count])
        </p>
        <a href="{{ route('monitor.analytics.export', array_merge(request()->query(), ['tab' => 'scores'])) }}"
            class="btn btn-secondary btn-xs">
            <i class="fa fa-download text-success mr-1" aria-hidden="true"></i>
            @lang('monitor::app.exportCsv')
        </a>
    </div>

    @if (!empty($browsing_summary['web_seconds']))
        <x-alert type="info" icon="globe" class="mb-3">
            @lang('monitor::app.browsingTimeSummary', [
                'time' => $browsing_summary['web_label'],
                'pct' => $browsing_summary['pct_of_tracked'],
                'domains' => $browsing_summary['unique_domains'],
            ])
        </x-alert>
    @endif

    @php
        $scoresQuery = array_filter(request()->only(['search', 'period']));
    @endphp
    <ul class="nav nav-pills monitor-scores-filters flex-wrap mb-3" role="tablist">
        <li class="nav-item mr-1 mb-2" role="presentation">
            <a href="{{ route('monitor.analytics.index', array_merge($scoresQuery, ['tab' => 'scores'])) }}"
                class="nav-link f-12 {{ !$department_id && !$below_sixty_only ? 'active' : '' }}">
                @lang('app.all')
            </a>
        </li>
        @foreach ($departments as $dept)
            <li class="nav-item mr-1 mb-2" role="presentation">
                <a href="{{ route('monitor.analytics.index', array_merge($scoresQuery, ['tab' => 'scores', 'department' => $dept->id])) }}"
                    class="nav-link f-12 {{ (int) $department_id === (int) $dept->id ? 'active' : '' }}">
                    {{ $dept->team_name }}
                </a>
            </li>
        @endforeach
        <li class="nav-item mr-1 mb-2" role="presentation">
            <a href="{{ route('monitor.analytics.index', array_merge($scoresQuery, ['tab' => 'scores', 'department' => $department_id, 'below_sixty' => 1])) }}"
                class="nav-link f-12 {{ $below_sixty_only ? 'active' : 'monitor-scores-filters__danger' }}">
                @lang('monitor::app.belowSixty')
            </a>
        </li>
    </ul>

    @if (!empty($top_unproductive_websites))
        <div class="card monitor-unproductive-panel border-0 b-shadow-4 mb-3">
            <div class="card-body p-20">
                <h4 class="f-14 f-w-500 text-darkest-grey mb-3">@lang('monitor::app.topUnproductiveWebsites')</h4>
                <ul class="list-group list-group-flush">
                    @foreach ($top_unproductive_websites as $item)
                        <li class="list-group-item border-0 px-3 py-2 mb-2 rounded">
                            <a href="{{ $item['rules_url'] }}" class="d-flex align-items-center text-dark-grey">
                                @include('monitor::analytics.partials.app-icon', [
                                    'size' => 28,
                                    'iconUrl' => $item['icon_url'],
                                    'letterAvatar' => $item['letter_avatar'],
                                    'alt' => $item['display_name'],
                                ])
                                <span class="flex-grow-1 ml-3 f-14 f-w-500 text-truncate">{{ $item['display_name'] }}</span>
                                <span class="f-14 text-dark-grey mr-3">{{ $item['duration_label'] }}</span>
                                <span class="f-12 text-red-600">{{ $item['pct_of_tracked'] }}%</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (!empty($top_unproductive))
        <div class="card monitor-unproductive-panel border-0 b-shadow-4 mb-3">
            <div class="card-body p-20">
                <h4 class="f-14 f-w-500 text-darkest-grey mb-3">@lang('monitor::app.topUnproductiveApps')</h4>
                <ul class="list-group list-group-flush">
                    @foreach ($top_unproductive as $item)
                        <li class="list-group-item border-0 px-3 py-2 mb-2 rounded">
                            <a href="{{ $item['rules_url'] }}" class="d-flex align-items-center text-dark-grey">
                                @include('monitor::analytics.partials.app-icon', [
                                    'size' => 28,
                                    'iconUrl' => $item['icon_url'],
                                    'letterAvatar' => $item['letter_avatar'],
                                    'alt' => $item['display_name'],
                                ])
                                <span class="flex-grow-1 ml-3 f-14 f-w-500 text-truncate">{{ $item['display_name'] }}</span>
                                <span class="f-14 text-dark-grey mr-3">{{ $item['duration_label'] }}</span>
                                <span class="f-12 text-red-600">{{ $item['pct_of_tracked'] }}%</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover w-100">
            <thead>
                <tr>
                    <th class="f-12 text-dark-grey" style="width: 48px;">#</th>
                    <th class="f-12 text-dark-grey">@lang('app.employee')</th>
                    <th class="f-12 text-dark-grey">@lang('app.menu.teams')</th>
                    <th class="f-12 text-dark-grey" style="min-width: 160px;">@lang('monitor::app.productivity')</th>
                    <th class="f-12 text-dark-grey text-right">@lang('monitor::app.activeTime')</th>
                    <th class="f-12 text-dark-grey" style="width: 96px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="f-14 text-lightest">{{ $row['rank'] }}</td>
                        <td class="f-14">
                            <div class="d-flex align-items-center">
                                <img src="{{ $row['image_url'] }}" alt="" class="rounded-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                @if ($row['is_online'])
                                    <span class="d-inline-block rounded-circle bg-green-500 mr-2" style="width: 8px; height: 8px;" title="@lang('monitor::app.onlineNow')"></span>
                                @endif
                                <span class="f-w-500 text-darkest-grey">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="f-14 text-dark-grey">{{ $row['department'] }}</td>
                        <td class="f-14">
                            <div class="d-flex align-items-center">
                                @include('monitor::analytics.partials.score-bar', ['score' => $row['score'], 'referenceScore' => $team_avg_score])
                                <span class="badge ml-2 {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreBadgeClass($row['score']) }}">
                                    {{ $row['score'] }}%
                                </span>
                            </div>
                        </td>
                        <td class="f-14 text-right text-dark-grey">{{ $row['active_hours_label'] }}</td>
                        <td class="f-14 text-right">
                            <a href="{{ $row['detail_url'] }}" class="f-12 text-primary">@lang('app.view')</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="f-14 text-lightest text-center p-20">@lang('monitor::app.noScoreData')</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($rows) > 0)
                <tfoot>
                    <tr class="bg-additional-grey">
                        <td colspan="3" class="f-14 f-w-500 text-dark-grey p-3">@lang('monitor::app.teamAverage')</td>
                        <td class="f-14 p-3">
                            <span class="badge {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreBadgeClass($team_avg_score) }}">
                                {{ $team_avg_score }}%
                            </span>
                        </td>
                        <td class="f-14 text-right p-3">{{ $team_avg_hours }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

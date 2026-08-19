@php
    $meta = $dashboard['meta'] ?? [];
    $teamHealthScore = (float) ($meta['team_health_score'] ?? 0);
    $teamHealthLabel = $meta['team_health_label'] ?? 'Good';
    $teamHealthTone = match ($teamHealthLabel) {
        'Excellent' => 'green',
        'Good' => 'green',
        'Needs Attention' => 'orange',
        'Critical' => 'red',
        default => 'gray',
    };
    $onlineCount = (int) ($meta['online_count'] ?? 0);
    $totalEmployees = (int) ($meta['total_employees'] ?? 0);
    $attentionCount = (int) ($meta['attention_count'] ?? 0);
    $alertTone = $attentionCount > 0 ? ($attentionCount >= 5 ? 'red' : 'orange') : 'green';
@endphp

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        @include('monitor::dashboard.partials.executive-metric-card', [
            'title' => 'Team Health',
            'value' => number_format($teamHealthScore, 0) . '/100',
            'subtitle' => 'Overall team status',
            'statusLabel' => $teamHealthLabel,
            'statusTone' => $teamHealthTone,
        ])
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        @include('monitor::dashboard.partials.executive-metric-card', [
            'title' => 'Online',
            'value' => $onlineCount . '/' . max(1, $totalEmployees),
            'subtitle' => number_format((float) ($meta['online_percentage'] ?? 0), 1) . '% of workforce online',
            'trend' => $meta['online_trend'] ?? null,
        ])
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        @include('monitor::dashboard.partials.executive-metric-card', [
            'title' => 'Productivity',
            'value' => number_format((float) ($meta['productivity_score'] ?? 0), 1) . '%',
            'subtitle' => 'Average productivity today',
            'statusLabel' => $meta['productivity_trend']['label'] ?? null,
            'statusTone' => 'gray',
            'showRing' => true,
            'progress' => (float) ($meta['productivity_score'] ?? 0),
        ])
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        @include('monitor::dashboard.partials.executive-metric-card', [
            'title' => 'Alerts',
            'value' => number_format($attentionCount),
            'subtitle' => $attentionCount > 0 ? 'Employees need review' : 'No open issues right now',
            'statusLabel' => $attentionCount > 0 ? 'Requires review' : 'All clear',
            'statusTone' => $alertTone,
        ])
    </div>
</div>

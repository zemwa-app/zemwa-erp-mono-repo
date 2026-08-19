<style>
    /* Monitor analytics — score tiers (used by MonitorAnalyticsHelper) */
    .bg-green-500 { background-color: #22c55e !important; }
    .bg-yellow-500 { background-color: #eab308 !important; }
    .bg-orange-500 { background-color: #f97316 !important; }
    .bg-red-500 { background-color: #ef4444 !important; }
    .bg-indigo-500 { background-color: #6366f1 !important; }

    .bg-green-100 { background-color: #dcfce7 !important; }
    .bg-yellow-100 { background-color: #fef9c3 !important; }
    .bg-orange-100 { background-color: #ffedd5 !important; }
    .bg-red-100 { background-color: #fee2e2 !important; }
    .bg-gray-100 { background-color: #f3f4f6 !important; }
    .bg-gray-200 { background-color: #e5e7eb !important; }
    .bg-blue-100 { background-color: #dbeafe !important; }
    .bg-amber-100 { background-color: #fef3c7 !important; }

    .text-green-600 { color: #16a34a !important; }
    .text-green-700 { color: #15803d !important; }
    .text-green-800 { color: #166534 !important; }
    .text-yellow-700 { color: #a16207 !important; }
    .text-yellow-800 { color: #854d0e !important; }
    .text-orange-700 { color: #c2410c !important; }
    .text-orange-800 { color: #9a3412 !important; }
    .text-red-600 { color: #dc2626 !important; }
    .text-red-700 { color: #b91c1c !important; }
    .text-red-800 { color: #991b1b !important; }
    .text-blue-800 { color: #1e40af !important; }
    .text-amber-600 { color: #d97706 !important; }
    .text-amber-800 { color: #92400e !important; }
    .text-gray-500 { color: #6b7280 !important; }
    .text-gray-600 { color: #4b5563 !important; }
    .text-gray-700 { color: #374151 !important; }

    /* Heatmap cells */
    .bg-teal-100 { background-color: #ccfbf1 !important; }
    .bg-teal-300 { background-color: #5eead4 !important; }
    .bg-teal-500 { background-color: #14b8a6 !important; }
    .bg-teal-700 { background-color: #0f766e !important; }
    .border-gray-300 { border-color: #d1d5db !important; }

    /* Analytics tabs */
    .monitor-analytics-tabs .analytics-tab-link {
        cursor: pointer;
    }

    .monitor-analytics-tabs .nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .monitor-analytics-tabs .nav .nav-item {
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Scores department filters */
    .monitor-scores-filters .nav-link {
        padding: 6px 14px;
        color: #616e80;
        background-color: #f3f4f6;
    }

    .monitor-scores-filters .nav-link:hover {
        color: #4b5563;
        background-color: #e5e7eb;
    }

    .monitor-scores-filters .nav-link.active {
        color: #fff !important;
        background-color: #1d82f5 !important;
    }

    .monitor-scores-filters .nav-link.monitor-scores-filters__danger {
        color: #d30000 !important;
        background-color: #fee2e2 !important;
    }

    .monitor-scores-filters .nav-link.monitor-scores-filters__danger:hover {
        color: #b91c1c !important;
        background-color: #fecaca !important;
    }

    body.dark-theme .monitor-scores-filters .nav-link:not(.active):not(.monitor-scores-filters__danger) {
        color: #d5d8df !important;
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-scores-filters .nav-link:not(.active):not(.monitor-scores-filters__danger):hover {
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-scores-filters .nav-link.monitor-scores-filters__danger {
        color: #fca5a5 !important;
        background-color: #3f1d1d !important;
    }

    body.dark-theme .monitor-scores-filters .nav-link.active {
        color: #181c34 !important;
        background-color: #d5d8df !important;
    }

    /* Score bar reference line */
    .monitor-score-bar .monitor-score-reference {
        position: absolute;
        top: 0;
        bottom: 0;
        border-left: 2px dashed #9ca3af;
        pointer-events: none;
    }

    /* Mini bar charts */
    .monitor-mini-bar-chart {
        display: flex;
        align-items: flex-end;
        height: 100px;
    }

    .monitor-mini-bar-chart .monitor-mini-bar-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        min-width: 16px;
    }

    .monitor-mini-bar-chart .monitor-mini-bar {
        width: 100%;
        max-width: 20px;
        border-radius: 3px 3px 0 0;
    }

    /* App icon */
    .monitor-app-icon {
        position: relative;
        display: inline-flex;
        flex-shrink: 0;
        overflow: hidden;
        border-radius: 4px;
        background-color: #f3f4f6;
    }

    .monitor-app-icon .monitor-app-icon-letter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        border-radius: 4px;
        font-weight: 600;
        color: #fff;
    }

    .monitor-app-icon img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 4px;
        object-fit: cover;
        background-color: #fff;
    }

    /* Usage progress */
    .monitor-usage-progress {
        height: 8px;
        background-color: #f3f4f6;
        border-radius: 999px;
        overflow: hidden;
    }

    .monitor-usage-progress .progress-bar {
        height: 100%;
        border-radius: 999px;
    }

    /* Heatmap */
    .monitor-heatmap-cell {
        width: 20px;
        height: 20px;
        border-radius: 3px;
    }

    .monitor-heatmap-legend span {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 3px;
        vertical-align: middle;
    }

    /* Idle active/idle bar */
    .monitor-idle-bar {
        height: 16px;
        border-radius: 999px;
        overflow: hidden;
        background-color: #fee2e2;
    }

    .monitor-idle-bar .monitor-idle-active {
        height: 100%;
    }

    /* Week summary pills */
    .monitor-week-day {
        min-width: 72px;
        text-align: center;
    }

    .monitor-week-day.is-today {
        border-color: #a5b4fc !important;
        background-color: #eef2ff !important;
    }

    /* Unproductive highlight panels */
    .monitor-unproductive-panel {
        border-color: #fecaca !important;
        background-color: #fef2f2;
    }

    .monitor-unproductive-panel .list-group-item {
        background-color: rgba(255, 255, 255, 0.85);
    }

    .monitor-unproductive-panel .list-group-item:hover {
        background-color: #fff;
    }

    /* Compliance composite */
    .monitor-compliance-score {
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1.1;
    }

    /* Categorize inline */
    .monitor-categorize-inline .form-control {
        width: auto;
        display: inline-block;
        height: 30px;
        padding: 2px 8px;
        font-size: 12px;
    }

    /* Shared visibility (table-search JS) */
    .hidden { display: none !important; }

    /* Report tabs */
    .monitor-report-tabs .report-tab-link {
        cursor: pointer;
        font-size: 13px;
        padding: 6px 14px;
        color: #616e80;
        background-color: #f3f4f6;
        border: 0;
    }

    .monitor-report-tabs .report-tab-link:hover {
        color: #4b5563;
        background-color: #e5e7eb;
    }

    .monitor-report-tabs .report-tab-link.active {
        color: #fff !important;
        background-color: #1d82f5 !important;
    }

    /* Reports filter bar */
    .monitor-reports-filter-bar__primary,
    .monitor-reports-filter-bar__secondary {
        width: 100%;
    }

    .monitor-reports-filter-bar__secondary {
        padding-top: 8px;
        margin-top: 4px;
    }

    .monitor-reports-filter-bar__employee {
        min-width: 180px;
    }

    .monitor-reports-filter-bar__duration {
        min-width: 220px;
    }

    .monitor-reports-filter-bar__duration #report-date-range {
        min-width: 210px;
        width: 100%;
    }

    .monitor-reports-filter-search {
        min-width: 240px;
    }

    body.dark-theme .monitor-report-tabs .report-tab-link:not(.active) {
        color: #d5d8df !important;
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-report-tabs .report-tab-link:not(.active):hover {
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-report-tabs .report-tab-link.active {
        color: #181c34 !important;
        background-color: #d5d8df !important;
    }

    body.dark-theme .monitor-reports-filter-bar__secondary {
        border-top-color: #4B4E69 !important;
    }

    /* Screenshot cards */
    .monitor-screenshot-card {
        display: block;
        height: 100%;
        border-radius: 4px;
        overflow: hidden;
        background-color: #fff;
        border: 1px solid #e8eef3;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .monitor-screenshot-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        text-decoration: none;
    }

    .monitor-screenshot-thumb {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        overflow: hidden;
        background-color: #f3f4f6;
    }

    .monitor-screenshot-thumb img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top;
    }

    /* Config setting rows */
    .config-setting-row {
        border-radius: 4px;
        padding: 12px;
    }

    .config-setting-row.is-nested {
        margin-left: 4px;
        border-left: 2px solid #e0e7ff;
        padding-left: 16px;
    }

    .monitor-setting-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .monitor-setting-icon--violet { background-color: #ede9fe; color: #7c3aed; }
    .monitor-setting-icon--emerald { background-color: #d1fae5; color: #059669; }
    .monitor-setting-icon--sky { background-color: #e0f2fe; color: #0284c7; }
    .monitor-setting-icon--amber { background-color: #fef3c7; color: #d97706; }
    .monitor-setting-icon--red { background-color: #fee2e2; color: #dc2626; }
    .monitor-setting-icon--grey { background-color: #f3f4f6; color: #4b5563; }

    .monitor-config-section-header {
        border-bottom: 1px solid #f1f1f3;
        background-color: #fafbfc;
    }

    .monitor-config-disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    /* Installer platform icons */
    .monitor-platform-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        font-size: 18px;
        flex-shrink: 0;
    }

    .monitor-platform-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 14px 16px;
        border-radius: 4px;
        border: 1px solid #e8eef3;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .monitor-platform-link.is-available {
        border-color: #c7d2fe;
        background-color: #eef2ff;
    }

    .monitor-platform-link.is-available:hover {
        border-color: #a5b4fc;
        background-color: #e0e7ff;
        text-decoration: none;
    }

    .monitor-platform-link.is-pending {
        border-color: #fde68a;
        background-color: #fffbeb;
    }

    .monitor-platform-link.is-pending:hover {
        border-color: #fcd34d;
        background-color: #fef3c7;
        text-decoration: none;
    }

    .monitor-installer-file-card.border-danger {
        border: 1px solid #d30000 !important;
    }

    .monitor-installer-upload-progress {
        padding: 16px;
        border-radius: 4px;
        border: 1px solid #c7d2fe;
        background-color: #f8f9ff;
    }

    .monitor-installer-progress {
        height: 10px;
        border-radius: 999px;
        background-color: #e8eef3;
        overflow: hidden;
    }

    .monitor-installer-progress .progress-bar {
        border-radius: 999px;
    }

    body.dark-theme .monitor-installer-upload-progress {
        background-color: #29304C !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-installer-progress {
        background-color: #181C34 !important;
    }

    .monitor-installer-source-tabs .nav {
        flex-wrap: nowrap;
    }

    .monitor-installer-source-tabs .monitor-installer-tab {
        cursor: pointer;
        border: 1px solid transparent !important;
        border-bottom: 1px solid #e8eef3 !important;
        border-radius: 4px 4px 0 0;
        color: #616e80;
        background: transparent !important;
        padding: 8px 14px;
        margin-bottom: -1px;
        white-space: nowrap;
    }

    .monitor-installer-source-tabs .monitor-installer-tab:hover {
        color: #1d82f5;
        background: #f8f9fa !important;
    }

    .monitor-installer-source-tabs .monitor-installer-tab.active {
        color: #1d82f5 !important;
        border-color: #e8eef3 !important;
        border-bottom-color: #fafbfc !important;
        background-color: #fafbfc !important;
        font-weight: 500;
    }

    .monitor-installer-source-tabs .monitor-installer-source-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .monitor-installer-tab-panel {
        background-color: #fafbfc;
        border-color: #e8eef3 !important;
    }

    body.dark-theme .monitor-installer-source-tabs .monitor-installer-tab:not(.active) {
        color: #d5d8df !important;
        border-bottom-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-installer-source-tabs .monitor-installer-tab.active {
        color: #1d82f5 !important;
        border-color: #4B4E69 !important;
        border-bottom-color: #181C34 !important;
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-installer-tab-panel {
        background-color: #181C34 !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-installer-file-selected {
        background-color: #29304C !important;
    }

    /* Productivity trend sparkline */
    .monitor-sparkline {
        display: flex;
        align-items: flex-end;
        height: 24px;
    }

    .monitor-sparkline-bar {
        width: 4px;
        margin-right: 2px;
        border-radius: 2px 2px 0 0;
        background-color: #818cf8;
    }

    /* Dynamic badge / icon shims from PHP helpers and installer config */
    .bg-indigo-50 { background-color: #eef2ff !important; }
    .bg-indigo-100 { background-color: #e0e7ff !important; }
    .bg-slate-100 { background-color: #f1f5f9 !important; }
    .bg-orange-50 { background-color: #fff7ed !important; }
    .bg-amber-50 { background-color: #fffbeb !important; }
    .bg-violet-100 { background-color: #ede9fe !important; }
    .bg-emerald-100 { background-color: #d1fae5 !important; }
    .bg-sky-100 { background-color: #e0f2fe !important; }

    .text-indigo-600 { color: #4f46e5 !important; }
    .text-indigo-700 { color: #4338ca !important; }
    .text-indigo-900 { color: #312e81 !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-orange-600 { color: #ea580c !important; }
    .text-violet-600 { color: #7c3aed !important; }
    .text-emerald-600 { color: #059669 !important; }
    .text-sky-600 { color: #0284c7 !important; }

    /* Employee detail tab bar */
    .monitor-detail-tabs .nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .monitor-detail-tabs .nav .nav-item {
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Employee health score ring */
    .monitor-health-score-ring {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(#1d82f5 calc(var(--score, 0) * 1%), #e2e8f0 0);
    }

    .monitor-health-score-ring__inner {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /* Dark theme — Monitor module overrides */
    body.dark-theme .monitor-health-score-ring {
        background: conic-gradient(#1d82f5 calc(var(--score, 0) * 1%), #29304C 0);
    }

    body.dark-theme .monitor-health-score-ring__inner {
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-screenshot-card {
        background-color: #181C34 !important;
        border-color: #4B4E69 !important;
        box-shadow: none;
    }

    body.dark-theme .monitor-screenshot-card:hover {
        border-color: #1d82f5 !important;
        box-shadow: none;
    }

    body.dark-theme .monitor-screenshot-thumb {
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-app-icon {
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-app-icon img {
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-usage-progress {
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-config-section-header {
        background-color: #29304C !important;
        border-bottom-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-unproductive-panel {
        background-color: #29304C !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-unproductive-panel .list-group-item {
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-unproductive-panel .list-group-item:hover {
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-week-day.is-today {
        border-color: #1d82f5 !important;
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-platform-link {
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-platform-link.is-available {
        border-color: #1d82f5 !important;
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-platform-link.is-available:hover {
        border-color: #1d82f5 !important;
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-platform-link.is-pending {
        border-color: #616e80 !important;
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-platform-link.is-pending:hover {
        border-color: #fcbd01 !important;
        background-color: #181C34 !important;
    }

    body.dark-theme .monitor-score-bar .monitor-score-reference {
        border-left-color: #616e80 !important;
    }

    /* Insight cards */
    .monitor-insight-item {
        border: 1px solid #e8eef3;
        border-left-width: 3px;
        border-radius: 4px;
        padding: 12px 14px;
        background-color: #fff;
    }

    .monitor-insight-item--positive {
        border-left-color: #28a745;
        background-color: #f8fcf9;
    }

    .monitor-insight-item--attention {
        border-left-color: #fcbd01;
        background-color: #fffdf5;
    }

    .monitor-insight-item--neutral {
        border-left-color: #616e80;
    }

    .monitor-insight-empty {
        border: 1px dashed #e8eef3;
        border-radius: 4px;
        padding: 20px;
        background-color: #fafbfc;
    }

    body.dark-theme .monitor-insight-item {
        background-color: #181C34 !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-insight-item--positive {
        border-left-color: #28a745 !important;
        background-color: #1a2e24 !important;
    }

    body.dark-theme .monitor-insight-item--attention {
        border-left-color: #fcbd01 !important;
        background-color: #2e2a1f !important;
    }

    body.dark-theme .monitor-insight-empty {
        background-color: #29304C !important;
        border-color: #4B4E69 !important;
    }

    /* Workday playback timeline */
    .monitor-playback-legend .badge {
        font-weight: 400;
    }

    .monitor-playback-hour {
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid #e8eef3;
    }

    .monitor-playback-hour:last-child {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .monitor-playback-hour__time {
        flex-shrink: 0;
        width: 52px;
        font-size: 12px;
        font-weight: 500;
        color: #616e80;
        text-align: right;
        padding-right: 12px;
        line-height: 32px;
    }

    .monitor-playback-hour__score {
        flex-shrink: 0;
        width: 72px;
        margin-left: 12px;
    }

    .monitor-playback-track {
        min-height: 32px;
        display: flex;
        overflow: hidden;
        border: 1px solid #e8eef3;
        border-radius: 4px;
        background-color: #fff;
    }

    .monitor-playback-hour--empty .monitor-playback-track {
        min-height: 28px;
        background-color: #fafbfc;
    }

    .monitor-playback-hour--empty .monitor-playback-hour__time {
        line-height: 28px;
    }

    .monitor-playback-track__empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #99a5b5;
    }

    .monitor-playback-segment {
        display: flex;
        align-items: center;
        overflow: hidden;
        padding: 0 8px;
        font-size: 11px;
        font-weight: 500;
        min-width: 0;
        transition: opacity 0.15s ease;
    }

    .monitor-playback-segment:hover {
        opacity: 0.92;
    }

    .monitor-playback-segment--high {
        background-color: #28a745;
        color: #fff;
    }

    .monitor-playback-segment--medium {
        background-color: #fcbd01;
        color: #343a40;
    }

    .monitor-playback-segment--low {
        background-color: #d30000;
        color: #fff;
    }

    .monitor-timeline-idle {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        background-color: #e9ecef;
        color: #6c757d;
    }

    .monitor-playback-hour__meta {
        margin-top: 10px;
        margin-left: 64px;
    }

    .monitor-playback-hour__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 16px;
        font-size: 12px;
        color: #616e80;
    }

    .monitor-playback-hour__stats span {
        white-space: nowrap;
    }

    .monitor-playback-hour__tags .badge {
        font-weight: 400;
    }

    .monitor-productivity-heatmap {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 12px;
    }

    .monitor-productivity-heatmap__item {
        min-width: 0;
        background-color: #fff;
    }

    .monitor-productivity-heatmap__label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .monitor-productivity-heatmap__bar {
        height: 40px;
        width: 100%;
    }

    .monitor-productivity-heatmap__bar--high {
        background-color: #28a745;
    }

    .monitor-productivity-heatmap__bar--medium {
        background-color: #fcbd01;
    }

    .monitor-productivity-heatmap__bar--low {
        background-color: #d30000;
    }

    .monitor-productivity-heatmap__bar--idle {
        background-color: #6c757d;
    }

    .monitor-trend-bar {
        border-radius: 4px 4px 0 0;
    }

    body.dark-theme .monitor-productivity-heatmap__item {
        background-color: #181C34 !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-playback-hour {
        border-bottom-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-playback-hour__time,
    body.dark-theme .monitor-playback-hour__stats {
        color: #9c9fa6 !important;
    }

    body.dark-theme .monitor-playback-track {
        background-color: #181C34 !important;
        border-color: #4B4E69 !important;
    }

    body.dark-theme .monitor-playback-hour--empty .monitor-playback-track {
        background-color: #29304C !important;
    }

    body.dark-theme .monitor-playback-track__empty {
        color: #9c9fa6 !important;
    }

    body.dark-theme .monitor-timeline-idle {
        background-color: #29304C !important;
        color: #9c9fa6 !important;
    }
</style>

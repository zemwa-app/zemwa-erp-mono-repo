@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@php
    $subcategoriesJson = json_encode($subcategories);
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="row mb-4">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <a href="{{ route('monitor.config.index') }}" class="f-14 text-dark-grey">
                    <i class="fa fa-arrow-left f-12 mr-1"></i>@lang('monitor::app.agentConfig')
                </a>
                <h2 class="f-21 font-weight-bold text-darkest-grey mt-2 mb-1">@lang('monitor::app.categoryRulesTitle')</h2>
                <p class="f-14 text-lightest mb-0">@lang('monitor::app.categoryRulesIntro')</p>
            </div>
            <div class="col-lg-4 text-lg-right">
                <x-forms.button-secondary class="mr-2 mb-2" type="button" id="reclassify-rules" icon="sync">
                    @lang('monitor::app.reclassifyNow')
                </x-forms.button-secondary>
                <x-forms.button-primary type="button" id="add-productivity-rule" icon="plus">
                    @lang('monitor::app.addRule')
                </x-forms.button-primary>
            </div>
        </div>

        <ul class="nav nav-pills mb-4">
            @foreach (['all' => __('monitor::app.tabAllRules'), 'overrides' => __('monitor::app.tabOrgOverrides'), 'uncategorised' => __('monitor::app.tabUncategorised')] as $tabKey => $tabLabel)
                <li class="nav-item mr-1 mb-1">
                    <a href="{{ route('monitor.config.rules.index', array_merge(request()->query(), ['tab' => $tabKey])) }}"
                        class="nav-link f-12 {{ $activeTab === $tabKey ? 'active' : '' }}">
                        {{ $tabLabel }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($activeTab !== 'uncategorised')
            <form id="productivity-rules-filter-form" method="GET" action="{{ route('monitor.config.rules.index') }}" class="card bg-white border-0 b-shadow-4 p-20 mb-4">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <label class="f-12 text-dark-grey d-block mb-1">@lang('app.category')</label>
                        <select name="category" class="select-picker form-control" data-size="8">
                            <option value="">@lang('app.all')</option>
                            @foreach (['productive' => 'categoryProductive', 'neutral' => 'categoryNeutral', 'unproductive' => 'categoryUnproductive'] as $cat => $langKey)
                                <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>@lang('monitor::app.' . $langKey)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <label class="f-12 text-dark-grey d-block mb-1">@lang('monitor::app.ruleType')</label>
                        <select name="type" class="select-picker form-control" data-size="8">
                            <option value="">@lang('app.all')</option>
                            <option value="url" @selected(($filters['type'] ?? '') === 'url')>URL</option>
                            <option value="app" @selected(($filters['type'] ?? '') === 'app')>App</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3 mb-md-0">
                        <label class="f-12 text-dark-grey d-block mb-1">@lang('app.search')</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control height-35" placeholder="@lang('monitor::app.searchPattern')">
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <x-forms.button-secondary type="submit" icon="filter">
                            @lang('app.apply')
                        </x-forms.button-secondary>
                    </div>
                </div>
            </form>

            <div class="card bg-white border-0 b-shadow-4 mb-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover w-100 f-14 mb-0">
                            <thead>
                                <tr class="text-uppercase f-11 text-lightest">
                                    <th class="pl-20">@lang('monitor::app.pattern')</th>
                                    <th>@lang('monitor::app.ruleType')</th>
                                    <th>@lang('app.category')</th>
                                    <th>@lang('monitor::app.subcategory')</th>
                                    <th>@lang('monitor::app.ruleSource')</th>
                                    <th class="text-right">@lang('monitor::app.timesMatched')</th>
                                    <th class="text-right pr-20">@lang('app.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rules as $rule)
                                    <tr>
                                        <td class="pl-20 f-w-500 text-darkest-grey">{{ $rule->pattern }}</td>
                                        <td>
                                            <span class="badge badge-secondary f-11 text-uppercase">{{ $rule->type }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::productivityCategoryBadgeClass($rule->category) }}">
                                                {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::productivityCategoryLabel($rule->category) }}
                                            </span>
                                        </td>
                                        <td class="text-dark-grey">{{ ucfirst(str_replace('_', ' ', $rule->subcategory)) }}</td>
                                        <td class="text-dark-grey">
                                            {{ $rule->isGlobal() ? __('monitor::app.globalDefault') : __('monitor::app.orgOverride') }}
                                        </td>
                                        <td class="text-right">{{ number_format($rule->match_count) }}</td>
                                        <td class="text-right pr-20">
                                            @if ($rule->isGlobal())
                                                <button type="button" class="btn btn-link p-0 f-12 override-rule-btn"
                                                    data-type="{{ $rule->type }}"
                                                    data-pattern="{{ $rule->pattern }}"
                                                    data-category="{{ $rule->category }}"
                                                    data-subcategory="{{ $rule->subcategory }}">
                                                    @lang('monitor::app.overrideRule')
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-link p-0 f-12 edit-rule-btn"
                                                    data-id="{{ $rule->id }}"
                                                    data-type="{{ $rule->type }}"
                                                    data-pattern="{{ $rule->pattern }}"
                                                    data-category="{{ $rule->category }}"
                                                    data-subcategory="{{ $rule->subcategory }}">@lang('app.edit')</button>
                                                <button type="button" class="btn btn-link p-0 f-12 text-danger ml-2 delete-rule-btn" data-id="{{ $rule->id }}">@lang('app.delete')</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 f-14 text-lightest">@lang('monitor::app.noRules')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($rules->hasPages())
                    <div class="card-footer bg-white border-top-grey px-20 py-3">{{ $rules->links() }}</div>
                @endif
            </div>
        @else
            @php
                $uncategorisedDomains = collect($uncategorised)->where('type', 'url')->values();
                $uncategorisedApps = collect($uncategorised)->where('type', 'app')->values();
            @endphp
            <p class="f-14 text-dark-grey mb-3">@lang('monitor::app.uncategorisedIntro')</p>

            <div class="monitor-search-panel mb-3">
                @include('monitor::partials.table-search', [
                    'id' => 'uncategorised-search',
                    'placeholder' => __('monitor::app.searchPattern'),
                ])
            </div>

            @foreach ([
                'url' => ['title' => __('monitor::app.uncategorisedDomains'), 'rows' => $uncategorisedDomains],
                'app' => ['title' => __('monitor::app.uncategorisedApps'), 'rows' => $uncategorisedApps],
            ] as $section)
                <div class="card bg-white border-0 b-shadow-4 mb-4">
                    <div class="card-header bg-white border-bottom-grey p-20">
                        <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $section['title'] }}</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="monitor-searchable-table table table-hover w-100 f-14 mb-0">
                                <thead>
                                    <tr class="text-uppercase f-11 text-lightest">
                                        <th class="pl-20">@lang('monitor::app.pattern')</th>
                                        <th class="text-right">@lang('app.duration')</th>
                                        <th class="text-right">@lang('monitor::app.employeesShort')</th>
                                        <th class="text-right pr-20">@lang('app.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($section['rows'] as $row)
                                        <tr class="monitor-search-row" data-search="{{ strtolower($row['pattern']) }}">
                                            <td class="pl-20 f-w-500 text-darkest-grey">{{ $row['pattern'] }}</td>
                                            <td class="text-right text-dark-grey">{{ $row['duration_label'] }}</td>
                                            <td class="text-right text-dark-grey">{{ $row['employee_count'] }}</td>
                                            <td class="text-right pr-20">
                                                @include('monitor::analytics.partials.categorize-inline', [
                                                    'item' => [
                                                        'type' => $row['type'],
                                                        'pattern' => $row['pattern'],
                                                    ],
                                                ])
                                                <button type="button" class="btn btn-link p-0 f-12 d-block mt-1 add-from-unknown-btn"
                                                    data-type="{{ $row['type'] }}"
                                                    data-pattern="{{ $row['pattern'] }}">@lang('monitor::app.customizeRule')</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 f-14 text-lightest">@lang('messages.noRecordFound')</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="modal fade" id="rule-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom-grey">
                    <h4 class="modal-title f-16 font-weight-bold mb-0" id="rule-modal-title">@lang('monitor::app.addRule')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-20">
                    <form id="rule-form">
                        <input type="hidden" name="rule_id" id="rule-id">
                        <div class="form-group">
                            <label class="f-12 text-dark-grey">@lang('monitor::app.ruleType')</label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline custom-control custom-radio mr-3">
                                    <input type="radio" name="type" value="url" id="rule-type-url" class="custom-control-input" checked>
                                    <label class="custom-control-label f-14" for="rule-type-url">URL</label>
                                </div>
                                <div class="form-check form-check-inline custom-control custom-radio">
                                    <input type="radio" name="type" value="app" id="rule-type-app" class="custom-control-input">
                                    <label class="custom-control-label f-14" for="rule-type-app">App</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="f-12 text-dark-grey" for="rule-pattern">@lang('monitor::app.pattern')</label>
                            <input type="text" name="pattern" id="rule-pattern" class="form-control height-35" placeholder="youtube.com">
                        </div>
                        <div class="form-group">
                            <label class="f-12 text-dark-grey" for="rule-category">@lang('app.category')</label>
                            <select name="category" id="rule-category" class="form-control height-35">
                                <option value="productive">@lang('monitor::app.categoryProductive')</option>
                                <option value="neutral">@lang('monitor::app.categoryNeutral')</option>
                                <option value="unproductive">@lang('monitor::app.categoryUnproductive')</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="f-12 text-dark-grey" for="rule-subcategory">@lang('monitor::app.subcategory')</label>
                            <select name="subcategory" id="rule-subcategory" class="form-control height-35"></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-grey">
                    <x-forms.button-secondary type="button" id="rule-modal-cancel">@lang('app.cancel')</x-forms.button-secondary>
                    <button type="submit" form="rule-form" class="btn btn-primary f-14">@lang('app.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            if (typeof refreshSelectPicker === 'function') {
                refreshSelectPicker('#productivity-rules-filter-form .select-picker');
            }

            const subcategories = {!! $subcategoriesJson !!};
            const $modal = $('#rule-modal');
            const form = document.getElementById('rule-form');
            const categoryEl = document.getElementById('rule-category');
            const subcategoryEl = document.getElementById('rule-subcategory');
            const patternEl = document.getElementById('rule-pattern');
            const ruleIdEl = document.getElementById('rule-id');

            function fillSubcategories(category, selected) {
                subcategoryEl.innerHTML = '';
                (subcategories[category] || []).forEach(function (sub) {
                    const opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub.replace(/_/g, ' ');
                    if (sub === selected) opt.selected = true;
                    subcategoryEl.appendChild(opt);
                });
            }

            function openModal(data) {
                ruleIdEl.value = data.id || '';
                document.querySelectorAll('input[name="type"]').forEach(function (r) {
                    r.checked = r.value === (data.type || 'url');
                });
                patternEl.value = data.pattern || '';
                categoryEl.value = data.category || 'productive';
                fillSubcategories(categoryEl.value, data.subcategory || '');
                patternEl.placeholder = (data.type || 'url') === 'app' ? 'code.exe' : 'youtube.com';
                $modal.modal('show');
            }

            function closeModal() {
                $modal.modal('hide');
            }

            categoryEl.addEventListener('change', function () {
                fillSubcategories(categoryEl.value, '');
            });

            document.querySelectorAll('input[name="type"]').forEach(function (r) {
                r.addEventListener('change', function () {
                    patternEl.placeholder = r.value === 'app' ? 'code.exe' : 'youtube.com';
                });
            });

            document.getElementById('add-productivity-rule')?.addEventListener('click', function () {
                openModal({});
            });

            document.querySelectorAll('.override-rule-btn, .edit-rule-btn, .add-from-unknown-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openModal({
                        id: btn.dataset.id || '',
                        type: btn.dataset.type,
                        pattern: btn.dataset.pattern,
                        category: btn.dataset.category || 'neutral',
                        subcategory: btn.dataset.subcategory || '',
                    });
                });
            });

            document.getElementById('rule-modal-cancel')?.addEventListener('click', closeModal);

            form?.addEventListener('submit', function (e) {
                e.preventDefault();
                const id = ruleIdEl.value;
                const url = id
                    ? '{{ url('account/monitor/config/rules') }}/' + id
                    : '{{ route('monitor.config.rules.store') }}';
                $.easyAjax({
                    url: url,
                    type: id ? 'PUT' : 'POST',
                    blockUI: true,
                    data: $(form).serialize(),
                    success: function () {
                        window.location.reload();
                    },
                });
            });

            document.querySelectorAll('.delete-rule-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!confirm('@lang('messages.deleteConfirm')')) return;
                    $.easyAjax({
                        url: '{{ url('account/monitor/config/rules') }}/' + btn.dataset.id,
                        type: 'DELETE',
                        blockUI: true,
                        success: function () {
                            window.location.reload();
                        },
                    });
                });
            });

            document.getElementById('productivity-rules-filter-form')?.addEventListener('submit', function () {
                $(this).find('.select-picker').each(function () {
                    const $select = $(this);
                    if ($select.data('selectpicker')) {
                        $select.selectpicker('refresh');
                    }
                });
            });

            document.getElementById('reclassify-rules')?.addEventListener('click', function () {
                $.easyAjax({
                    url: '{{ route('monitor.config.rules.reclassify') }}',
                    type: 'POST',
                    blockUI: true,
                    success: function (response) {
                        if (typeof showToast === 'function') {
                            showToast('success', response.message || '@lang('monitor::app.reclassifyQueued')');
                        }
                    },
                });
            });
        })();
    </script>
@endpush

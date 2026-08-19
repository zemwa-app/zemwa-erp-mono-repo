@php
    use Modules\Monitor\Entities\ProductivityRule;

    $item = $item ?? [];
    $canCategorize = in_array(user()->permission('view_monitor'), ['all', 'added'], true)
        && !empty($item['pattern'])
        && !empty($item['type']);
@endphp

@if ($canCategorize)
    <div class="monitor-categorize-inline d-flex flex-wrap align-items-center justify-content-end ml-3"
        data-type="{{ $item['type'] }}"
        data-pattern="{{ $item['pattern'] }}">
        <select class="monitor-cat-category form-control mr-1"
            aria-label="@lang('app.category')">
            <option value="productive">@lang('monitor::app.categoryProductive')</option>
            <option value="neutral" selected>@lang('monitor::app.categoryNeutral')</option>
            <option value="unproductive">@lang('monitor::app.categoryUnproductive')</option>
        </select>
        <select class="monitor-cat-subcategory form-control mr-1"
            aria-label="@lang('monitor::app.subcategory')"></select>
        <button type="button"
            class="monitor-cat-save btn btn-primary btn-xs">
            @lang('app.save')
        </button>
    </div>
@else
    <span class="badge badge-secondary ml-3">@lang('monitor::app.uncategorised')</span>
    @if (!empty($item['rules_url']))
        <a href="{{ $item['rules_url'] }}" class="f-12 text-primary ml-2">@lang('monitor::app.addRule')</a>
    @endif
@endif

@once
    @push('scripts')
        <script>
            (function () {
                const subcategories = @json(ProductivityRule::subcategoriesByCategory());
                const storeUrl = @json(route('monitor.config.rules.store'));

                function fillSubcategorySelect($wrap, category, selected) {
                    const $sub = $wrap.find('.monitor-cat-subcategory');
                    $sub.empty();
                    (subcategories[category] || []).forEach(function (sub) {
                        const $opt = $('<option></option>').val(sub).text(sub.replace(/_/g, ' '));
                        if (sub === selected) {
                            $opt.prop('selected', true);
                        }
                        $sub.append($opt);
                    });
                }

                function initCategorizeInline($wrap) {
                    if ($wrap.data('categorizeInit')) {
                        return;
                    }
                    $wrap.data('categorizeInit', true);
                    const category = $wrap.find('.monitor-cat-category').val() || 'neutral';
                    const subs = subcategories[category] || [];
                    fillSubcategorySelect($wrap, category, subs[0] || '');
                }

                function initAll() {
                    $('.monitor-categorize-inline').each(function () {
                        initCategorizeInline($(this));
                    });
                }

                $('body').on('change', '.monitor-cat-category', function () {
                    const $wrap = $(this).closest('.monitor-categorize-inline');
                    fillSubcategorySelect($wrap, $(this).val(), '');
                });

                $('body').on('click', '.monitor-cat-save', function () {
                    const $btn = $(this);
                    const $wrap = $btn.closest('.monitor-categorize-inline');
                    const $category = $wrap.find('.monitor-cat-category');
                    const $subcategory = $wrap.find('.monitor-cat-subcategory');

                    $btn.prop('disabled', true);

                    $.easyAjax({
                        url: storeUrl,
                        type: 'POST',
                        blockUI: true,
                        data: {
                            _token: '{{ csrf_token() }}',
                            type: $wrap.data('type'),
                            pattern: $wrap.data('pattern'),
                            category: $category.val(),
                            subcategory: $subcategory.val(),
                        },
                        success: function (response) {
                            if (typeof showToast === 'function') {
                                showToast('success', response.message || '@lang('monitor::app.ruleSaved')');
                            }
                            window.location.reload();
                        },
                        error: function () {
                            $btn.prop('disabled', false);
                        },
                    });
                });

                initAll();
                $(document).on('ajaxComplete', initAll);
            })();
        </script>
    @endpush
@endonce

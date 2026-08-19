@props([
    'id' => 'monitor-table-search',
    'placeholder' => null,
])
<div class="mb-3 monitor-table-search-wrap">
    <div class="input-group bg-grey rounded">
        <div class="input-group-prepend">
            <span class="input-group-text border-0 bg-additional-grey">
                <i class="fa fa-search f-13 text-dark-grey" aria-hidden="true"></i>
            </span>
        </div>
        <input type="text"
            id="{{ $id }}"
            placeholder="{{ $placeholder ?? __('app.search') }}"
            class="monitor-table-search-input form-control f-14 p-1 border-additional-grey"
            autocomplete="off">
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const bindTableSearch = () => {
                    $('.monitor-table-search-input').each(function () {
                        const $input = $(this);
                        if ($input.data('searchBound')) {
                            return;
                        }
                        $input.data('searchBound', true);

                        const $tables = $input.closest('.monitor-search-panel').find('.monitor-searchable-table');
                        if (!$tables.length) {
                            return;
                        }

                        const applyFilter = () => {
                            const q = ($input.val() || '').toLowerCase().trim();

                            $tables.each(function () {
                                const $table = $(this);
                                const $rows = $table.find('tbody tr.monitor-search-row');
                                const $empty = $table.find('tr.monitor-search-empty');
                                let visible = 0;

                                $rows.each(function () {
                                    const $row = $(this);
                                    const haystack = String($row.data('search') || '').toLowerCase();
                                    const show = q === '' || haystack.includes(q);
                                    $row.toggleClass('hidden', !show);

                                    let $next = $row.next();
                                    while ($next.length && !$next.hasClass('monitor-search-row')) {
                                        $next.toggleClass('hidden', !show);
                                        $next = $next.next();
                                    }

                                    if (show) {
                                        visible++;
                                    }
                                });

                                if ($empty.length) {
                                    $empty.toggleClass('hidden', visible > 0 || $rows.length === 0);
                                }
                            });
                        };

                        $input.on('input', applyFilter);
                    });
                };

                bindTableSearch();
                $(document).on('ajaxComplete', bindTableSearch);
            })();
        </script>
    @endpush
@endonce

@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE RANGE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE RANGE END -->

        <!-- PRODUCT SELECT START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.menu.products')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="filter_product_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @php
                        // Fetch all products for the company to let them navigate
                        $allProducts = \App\Models\Product::select('id', 'name', 'sku')->where('company_id', company()->id)->get();
                    @endphp
                    @foreach ($allProducts as $p)
                        <option value="{{ $p->id }}" {{ $p->id == $productId ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->sku ? '(' . $p->sku . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PRODUCT SELECT END -->

        <!-- TRANSACTION TYPE START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.type')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="filter_transaction_type">
                    <option value="all">@lang('app.all')</option>
                    <option value="sale">Sale</option>
                    <option value="purchase">Purchase</option>
                    <option value="adjustment">Adjust Stock</option>
                </select>
            </div>
        </div>
        <!-- TRANSACTION TYPE END -->

        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-0-lg">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="d-flex mb-3 justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="{{ route('product-ledger.index') }}" class="btn btn-secondary btn-sm mr-3">
                    <i class="fa fa-arrow-left"></i> @lang('app.back') to Global Ledger
                </a>
                <h4 class="mb-0 font-weight-bold text-darkest-grey">{{ $product->name }} {{ $product->sku ? '(' . $product->sku . ')' : '' }}</h4>
            </div>
            <div id="table-actions" class="align-items-center">
                <!-- Standard action container -->
            </div>
        </div>

        <!-- 4 Card Widgets -->
        <div class="row mb-4 mt-2">
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <x-cards.widget :title="'Total Sold'" value="0" icon="shopping-bag" widgetId="total-sold-card" />
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <x-cards.widget :title="'Total Purchased'" value="0" icon="truck" widgetId="total-purchased-card" />
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <x-cards.widget :title="'Total Adjusted'" value="0" icon="sliders" widgetId="total-adjusted-card" />
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <x-cards.widget :title="'Current Stock'" value="0" icon="box" widgetId="current-stock-card" />
            </div>
        </div>

        <!-- Table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Table Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#product-ledger-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var type = $('#filter_transaction_type').val();
            var searchText = $('#search-text-field').val();

            data['product_id'] = "{{ $productId }}";
            data['type'] = type;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });

        const showTable = () => {
            window.LaravelDataTables["product-ledger-table"].draw(true);
            updateSummaryCards();
        }

        function updateSummaryCards() {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();
            var endDate = null;

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            $.easyAjax({
                url: "{{ route('product-ledger.summary') }}",
                type: "GET",
                data: {
                    product_id: "{{ $productId }}",
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#total-sold-card').text(response.totalSold);
                        $('#total-purchased-card').text(response.totalPurchased);
                        $('#total-adjusted-card').text(response.totalAdjusted);
                        $('#current-stock-card').text(response.currentStock);
                    }
                }
            });
        }

        // Initial card update
        $(document).ready(function() {
            updateSummaryCards();
        });

        $('#filter_product_id').on('change', function() {
            var val = $(this).val();
            if (val == 'all') {
                window.location.href = "{{ route('product-ledger.index') }}";
            } else {
                window.location.href = "{{ route('product-ledger.index') }}/" + val;
            }
        });

        $('#filter_transaction_type').on('change keyup', function() {
            if ($('#filter_transaction_type').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                if ($('#search-text-field').val() == "" && $('#datatableRange').val() == "") {
                    $('#reset-filters').addClass('d-none');
                }
            }
            showTable();
        });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
            } else {
                if ($('#filter_transaction_type').val() == "all" && $('#datatableRange').val() == "") {
                    $('#reset-filters').addClass('d-none');
                }
            }
            showTable();
        });

        $('#datatableRange').on('apply.daterangepicker', function(ev, picker) {
            $('#reset-filters').removeClass('d-none');
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#filter_transaction_type').val('all').selectpicker('refresh');
            $('#search-text-field').val('');
            $('#datatableRange').val('');
            $('#reset-filters').addClass('d-none');
            showTable();
        });
    </script>
@endpush

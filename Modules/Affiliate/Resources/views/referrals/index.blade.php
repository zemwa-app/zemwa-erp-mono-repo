@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>

        <!-- CATEGORY START -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                @lang('affiliate::app.customer')</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="company_id" id="company_id">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CATEGORY END -->

        <!-- SUBCATEGORY START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                @lang('affiliate::app.affiliate')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="affiliate_id" id="affiliate_id">
                    <option selected value="all">@lang('app.all')</option>
                    @foreach ($affiliates as $affiliate)
                        <option value="{{ $affiliate->id }}">{{ $affiliate->user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- SUBCATEGORY END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
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
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>

@endsection

@php
$addReferralPermission = user()->permission('add_referrals');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Referral Buttons Start -->
        <input type="hidden" name="user_id" class="user_id" value={{user()->id}}>
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addReferralPermission == 'all' || $addReferralPermission == 'added')
                    <x-forms.link-primary :link="route('referral.create')" class="mr-3 openRightModal float-left"
                        icon="plus">
                        @lang('app.add')
                        @lang('affiliate::app.menu.referralsCommissions')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>
        <!-- Add Referral Buttons End -->

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
"use strict";  // Enforces strict mode for the entire script

        $('#referrals-table').on('preXhr.dt', function(e, settings, data) {
            var searchText = $('#search-text-field').val();
            var companyID = $('#company_id').val();
            var affiliateID = $('#affiliate_id').val();

            data['affiliate_id'] = affiliateID;
            data['searchText'] = searchText;
            data['company_id'] = companyID;
        });
        const showTable = () => {
            window.LaravelDataTables["referrals-table"].draw(true);
        }

        $('#company_id, #affiliate_id').on('change keyup', function() {
            if ($('#company_id').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#affiliate_id').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else{
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');

                showTable();
            }
        });

        $('body').on('click', '#reset-filters', function () {
            $('#filter-form')[0].reset();

            $('#company_id').val('all');
            $('#affiliate_id').val('all');

            $('.select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('referral-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('referral.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush

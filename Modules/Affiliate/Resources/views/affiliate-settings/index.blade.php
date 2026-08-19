@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <div class="row">

                    <div class="col-md-4">
                        <x-forms.select fieldId="commission_enabled" :fieldLabel="__('affiliate::modules.settings.commissionEnabled')" fieldName="commission_enabled" search="true">
                            @foreach (\Modules\Affiliate\Enums\YesNo::cases() as $commission)
                                <option value="{{ $commission->value }}" @selected($settings->commission_enabled == $commission)>{{ $commission->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="payout_type" :fieldLabel="__('affiliate::app.payoutType')" fieldName="payout_type" search="true">
                            @foreach (\Modules\Affiliate\Enums\PayoutType::cases() as $type)
                                <option value="{{ $type->value }}" @selected($settings->payout_type == $type)>{{ $type->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4 @if($settings->payout_time == null) d-none @endif" id="payout_time_div">
                        <x-forms.select fieldId="payout_time" :fieldLabel="__('affiliate::app.payoutTime')" fieldName="payout_time" search="true">
                            @foreach (\Modules\Affiliate\Enums\PayoutTime::cases() as $type)
                                <option value="{{ $type->value }}" @selected($settings->payout_time == $type)>{{ $type->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="commission_type" :fieldLabel="__('affiliate::modules.settings.commissionType')" fieldName="commission_type" search="true">
                            @foreach (\Modules\Affiliate\Enums\CommissionType::cases() as $type)
                                <option value="{{ $type->value }}" @selected($settings->commission_type == $type)>{{ $type->label() }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldId="commission_cap" fieldRequired="true" :fieldLabel="__('affiliate::modules.settings.commissionCap')" fieldName="commission_cap" :fieldPlaceholder="__('affiliate::modules.settings.commissionCap')" fieldValue="{{ $settings->commission_cap }}">
                        </x-forms.number>
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldId="minimum_payout" fieldRequired="true" :fieldLabel="__('affiliate::modules.settings.minimumPayout')" fieldName="minimum_payout" :fieldPlaceholder="__('affiliate::modules.settings.minimumPayout')" fieldValue="{{ $settings->minimum_payout }}">
                        </x-forms.number>
                    </div>

                </div>
            </div>

            <!-- Buttons Start -->
            <div class="w-100 border-top-grey set-btns">
                <x-setting-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                </x-setting-form-actions>
            </div>

        </x-setting-card>
    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')

    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
        <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    @endif

    <script>
"use strict";  // Enforces strict mode for the entire script
        function handlePayoutTypeChange() {
            let type = $('#payout_type').val();

            if (type == 'after signup') {
                $('#payout_time_div').removeClass('d-none');
                $('#commission_type').prop('disabled', false).selectpicker("refresh");
                $('#commission_type').selectpicker('destroy').selectpicker();
            } else {
                $('#payout_time_div').addClass('d-none');
                $('#commission_type').val('fixed').prop('disabled', true).selectpicker("refresh");
            }
        }

        $(document).ready(function() {
            handlePayoutTypeChange();
        });

        $('#payout_type').on('change', handlePayoutTypeChange);
        $('#save-form').on('click', function(e) {
            var url = "{{ route('affiliate-settings.update', [$settings->id]) }}";
            $('#commission_type').prop('disabled', false);
            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: "#save-form",
                file: true,
                data: $('#editSettings').serialize(),
            });
        });
    </script>
@endpush

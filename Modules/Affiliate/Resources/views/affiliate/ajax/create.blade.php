@php
    $changeSuperadminRolePermission = user()->permission('change_superadmin_role');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="affiliates-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-3 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('affiliate::app.createAffiliate')</h4>

                <div class="row px-3">

                    @include('common.smtp-error')

                    <div class="col-lg-9 col-xl-10 ">
                        <div class="row my-3">
                            <div class="col-md-12">

                                <div id="alert"></div>
                            </div>
                            <div class="col-md-5">
                                <x-forms.select fieldId="user_id"
                                                :fieldLabel="__('affiliate::app.affiliateName')"
                                                fieldName="user_id"
                                                search="true" alignRight="true" fieldRequired="true">
                                    <option value="">--</option>
                                    @foreach ($users as $user)
                                        <x-user-option :user="$user" :additionalText="$user->clientDetails?->company_name" />
                                    @endforeach
                                </x-forms.select>
                            </div>
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-affiliates-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('affiliate.index')"
                                           class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
"use strict";  // Enforces strict mode for the entire script

    $(document).ready(function () {

        $('body').on('click', '#save-affiliates-form', function () {
            $.easyAjax({
                url: "{{ route('affiliate.store') }}",
                container: '#affiliates-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-affiliates-form",
                data: $('#affiliates-form').serialize(),

            });
        });


        $('.cropper').on('dropify.fileReady', function (e) {
            const inputId = $(this).find('input').attr('id');
            let url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        init(RIGHT_MODAL);
    });


</script>

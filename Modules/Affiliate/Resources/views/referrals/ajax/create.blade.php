<div class="row">
    <div class="col-sm-12">
        <x-form id="save-referral-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-3 f-21 font-weight-normal text-capitalize border-bottom-grey">{{ $pageTitle }}</h4>

                <div class="row px-3 mb-3">
                    <div class="col-lg-12 col-xl-10">
                        <div class="row">
                            <div class="col-md-4">
                                <x-forms.select fieldId="company_id" :fieldLabel="__('affiliate::app.customer')" fieldName="company_id">
                                    <option value="">--</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="affiliate_id" :fieldLabel="__('affiliate::app.affiliate')" fieldName="affiliate_id">
                                    @foreach ($affiliates as $affiliate)
                                        <option value="{{ $affiliate->id }}">{{ $affiliate->user->name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-referral-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('referral.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
"use strict";  // Enforces strict mode for the entire script
    $(document).ready(function() {

        $('body').on('click', '#save-referral-form', function () {
            $.easyAjax({
                url: "{{ route('referral.store') }}",
                container: '#save-referral-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-referral-form",
                data: $('#save-referral-data-form').serialize(),
            });
        });

        $('body').on('change', '#company_id', function () {

            var url = "{{ route('affiliates.get_affiliates', ':company') }}";
            url = url.replace(':company', $(this).val());

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' + value.user.name + '</option>';
                            options.push(selectData);
                        });

                        $('#affiliate_id').html(options);
                        $('#affiliate_id').selectpicker('refresh');
                    }
                }
            })

        });

        init(RIGHT_MODAL);
    });
</script>

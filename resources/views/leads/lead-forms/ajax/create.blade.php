<div class="row">
    <div class="col-sm-12">
        <x-form id="addLeadForm" method="POST" class="ajax-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('modules.lead.addLeadForm')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="slug" :fieldLabel="__('modules.lead.formSlug')" fieldName="slug"
                                      :fieldPlaceholder="__('modules.lead.formSlugHelp')"
                                      :fieldHelp="__('modules.lead.formSlugHelp')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="active">@lang('app.active')</option>
                            <option value="inactive">@lang('app.inactive')</option>
                        </x-forms.select>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey border-top-grey">
                    @lang('modules.lead.submissionSettings')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="lead_pipeline_id" :fieldLabel="__('modules.deal.pipeline')" fieldName="lead_pipeline_id">
                            <option value="">--</option>
                            @foreach ($leadPipelines as $pipeline)
                                <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="pipeline_stage_id" :fieldLabel="__('modules.deal.stages')" fieldName="pipeline_stage_id">
                            <option value="">--</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="category_id" :fieldLabel="__('modules.deal.dealCategory')" fieldName="category_id">
                            <option value="">--</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="lead_source_id" :fieldLabel="__('modules.lead.leadSource')" fieldName="lead_source_id">
                            <option value="">--</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source->id }}">{{ $source->type }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-lead-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel class="border-0 close-lead-form-panel">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select-picker').selectpicker('refresh');
        getStages($('#lead_pipeline_id').val());

        $('#lead_pipeline_id').on('change', function() {
            getStages($(this).val());
        });
    });

    function getStages(pipelineId) {
        if (!pipelineId) {
            $('#pipeline_stage_id').html('<option value="">--</option>').selectpicker('refresh');
            return;
        }

        var url = "{{ route('deals.get-stage', ':id') }}".replace(':id', pipelineId);
        $.easyAjax({
            url: url,
            type: "GET",
            success: function (response) {
                if (response.status == 'success') {
                    var options = ['<option value="">--</option>'];
                    $.each(response.data, function (index, value) {
                        options.push('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#pipeline_stage_id').html(options.join(''));
                    $('#pipeline_stage_id').selectpicker('refresh');
                }
            }
        });
    }

    $('.close-lead-form-panel').click(function() {
        if ($(RIGHT_MODAL).hasClass('in')) {
            document.getElementById('close-task-detail').click();
        }
    });

    $('#save-lead-form').click(function() {
        $.easyAjax({
            url: "{{ route('lead-forms.store') }}",
            container: '#addLeadForm',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#save-lead-form",
            data: $('#addLeadForm').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.href = "{{ route('lead-forms.index') }}";
                }
            }
        });
    });
</script>

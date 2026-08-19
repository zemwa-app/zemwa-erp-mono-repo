<div class="row">
    <div class="col-sm-12">
        <x-form id="editLeadForm" method="PUT" class="ajax-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('app.edit') @lang('modules.lead.leadForm')
                </h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"
                                      :fieldValue="$leadForm->name" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="slug" :fieldLabel="__('modules.lead.formSlug')" fieldName="slug"
                                      :fieldValue="$leadForm->slug"
                                      :fieldHelp="__('modules.lead.formSlugHelp')" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option @selected($leadForm->status == 'active') value="active">@lang('app.active')</option>
                            <option @selected($leadForm->status == 'inactive') value="inactive">@lang('app.inactive')</option>
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
                                <option @selected($leadForm->lead_pipeline_id == $pipeline->id) value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="pipeline_stage_id" :fieldLabel="__('modules.deal.stages')" fieldName="pipeline_stage_id">
                            <option value="">--</option>
                            @foreach ($stages as $stage)
                                @if ($stage->lead_pipeline_id == $leadForm->lead_pipeline_id)
                                    <option @selected($leadForm->pipeline_stage_id == $stage->id) value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endif
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="category_id" :fieldLabel="__('modules.deal.dealCategory')" fieldName="category_id">
                            <option value="">--</option>
                            @foreach ($categories as $category)
                                <option @selected($leadForm->category_id == $category->id) value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="lead_source_id" :fieldLabel="__('modules.lead.leadSource')" fieldName="lead_source_id">
                            <option value="">--</option>
                            @foreach ($sources as $source)
                                <option @selected($leadForm->lead_source_id == $source->id) value="{{ $source->id }}">{{ $source->type }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-lead-form" class="mr-3" icon="check">@lang('app.save')
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
        var selectedStage = "{{ $leadForm->pipeline_stage_id }}";
        $.easyAjax({
            url: url,
            type: "GET",
            success: function (response) {
                if (response.status == 'success') {
                    var options = ['<option value="">--</option>'];
                    $.each(response.data, function (index, value) {
                        var selected = selectedStage == value.id ? 'selected' : '';
                        options.push('<option ' + selected + ' value="' + value.id + '">' + value.name + '</option>');
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

    $('#update-lead-form').click(function() {
        $.easyAjax({
            url: "{{ route('lead-forms.update', $leadForm->id) }}",
            container: '#editLeadForm',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#update-lead-form",
            data: $('#editLeadForm').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });
</script>

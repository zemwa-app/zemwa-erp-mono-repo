<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">{{ ucfirst($platform) }} @lang('restapi::app.devices')</h5>
    <button type="button" onclick="removeOpenModal()" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="sendPushNotification" method="POST" class="ajax-form">
            <input type="hidden" name="platform" value="{{ $platform }}">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        @if($devices->count())
                            <div class="form-group my-2">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="f-14 text-dark-grey mb-0"
                                           for="usr">@lang('restapi::app.selectDevice')</label>
                                    <span class="badge badge-primary">{{ $devices->count() }} @lang('restapi::app.devices')</span>
                                </div>

                                <div class="border rounded p-3 bg-light">
                                    <div style="max-height: 380px; overflow-y: auto; padding-right: 4px;">
                                    @foreach($devices as $device)
                                        @php
                                            $meta = json_decode($device->details ?? '', true);
                                            $meta = is_array($meta) ? $meta : [];
                                        @endphp
                                        <label class="w-100 border rounded bg-white px-3 py-2 mb-2 cursor-pointer device-option" for="device_{{ $device->id }}">
                                            <div class="d-flex align-items-center">
                                                <input class="mr-2" type="radio" name="device_id" id="device_{{ $device->id }}"
                                                       value="{{ $device->registration_id }}" {{ $loop->first ? 'checked' : '' }}>

                                                <div class="w-100 d-flex align-items-center justify-content-between flex-wrap">
                                                    <div class="mr-3 d-flex align-items-center flex-wrap">
                                                        @if($device->user)
                                                            <div class="mr-3">
                                                                {!! view('components.employee', ['user' => $device->user])->render() !!}
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                        <span class="text-muted"><strong>@lang('restapi::app.appVersion'):</strong> {{ $meta['appVersion'] ?? '-' }}</span>
                                                    </div>

                                                    <div class="text-muted d-flex align-items-center flex-nowrap" style="min-width: 360px; justify-content: flex-end;">
                                                        <div class="text-right">
                                                            <div><strong>@lang('restapi::app.deviceId'):</strong> {{ $device->device_id }}</div>
                                                            <small class="d-block"><strong>@lang('restapi::app.registeredOn'):</strong> {{ $device->created_at ? $device->created_at->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format) : '-' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                @lang('restapi::app.noRegisterDeviceFound')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary :disabled="!$devices->count()" id="send-push-notification"
                            icon="check">@lang('app.send')</x-forms.button-primary>
</div>

<script>

    // save source
    $('#send-push-notification').click(function () {
        $.easyAjax({
            url: "{{ route('rest-api.send_push') }}",
            container: '#sendPushNotification',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#send-push-notification",
            data: $('#sendPushNotification').serialize(),
            success: function (response) {
                if (response.status == "success") {
                    $(MODAL_LG).modal('hide');
                }
            }
        })
    });

    function updateSelectedDeviceRow() {
        $('.device-option').removeClass('border-primary');
        $("input[name='device_id']:checked").closest('.device-option').addClass('border-primary');
    }

    $(document).on('change', "input[name='device_id']", function () {
        updateSelectedDeviceRow();
    });

    updateSelectedDeviceRow();
</script>

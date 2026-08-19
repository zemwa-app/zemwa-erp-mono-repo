<style>
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-active {
        background-color: #28a745;
        color: white;
    }
    .status-inactive {
        background-color: #dc3545;
        color: white;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('Manage Server Types')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<div class="modal-body">
    <x-table class="table-bordered" headType="thead-light">
        <x-slot name="thead">
            <th>#</th>
            <th>@lang('Name')</th>
            <th>@lang('app.description')</th>
            <th>@lang('app.status')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($serverTypes as $key => $item)
            <tr id="servertype-{{ $item->id }}">
                <td>{{ $key + 1 }}</td>
                <td data-row-id="{{ $item->id }}" data-column="name" contenteditable="true">
                    {!! $item->name !!}
                </td>
                <td data-row-id="{{ $item->id }}" data-column="description" contenteditable="true">
                    {!! $item->description !!}
                </td>
                <td data-row-id="{{ $item->id }}" data-column="status">
                    <select class="form-control select-picker change-status" name="status"
                        data-servertype-id="{{ $item->id }}">
                        <option value="active" @selected($item->status == 'active')>Active</option>
                        <option value="inactive" @selected($item->status == 'inactive')>Inactive</option>
                    </select>
                </td>
                <td class="text-right">
                    @if (user()->permission('manage_servertype') == 'all')
                        <x-forms.button-secondary data-servertype-id="{{ $item->id }}" icon="trash" class="delete-servertype">
                            @lang('app.delete')
                        </x-forms.button-secondary>
                    @endif
                </td>
            </tr>
        @empty
            <x-cards.no-record-found-list colspan="5" />
        @endforelse
    </x-table>

    <x-form id="createServerTypeForm">
        <div class="row border-top-grey mt-3 pt-3">
            <div class="col-md-6">
                <x-forms.text
                    fieldId="name"
                    :fieldLabel="__('Name')"
                    fieldName="name"
                    fieldRequired="true"
                    :fieldPlaceholder="__('Enter server type name')">
                </x-forms.text>
            </div>

            <div class="col-md-6">
                <x-forms.select
                    fieldId="status"
                    :fieldLabel="__('app.status')"
                    fieldName="status"
                    fieldRequired="true">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </x-forms.select>
            </div>

            <div class="col-sm-12 col-md-12">
                <x-forms.textarea
                    :fieldLabel="__('app.description')"
                    fieldName="description"
                    fieldId="description"
                    :fieldPlaceholder="__('Enter description (optional)')">
                </x-forms.textarea>
            </div>
        </div>
    </x-form>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-servertype" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(".select-picker").selectpicker();

    // Delete server type
    $('.delete-servertype').click(function() {
        var id = $(this).data('servertype-id');
        var url = "{{ route('server-type.destroy', ':id') }}";
        url = url.replace(':id', id);
        var token = "{{ csrf_token() }}";

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
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#servertype-' + id).remove();
                            // Find server_type dropdown in both RIGHT_MODAL and regular page contexts
                            var $serverTypeSelect = null;
                            if (typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                                $serverTypeSelect = $(RIGHT_MODAL_CONTENT).find('#server_type');
                            }
                            if (!$serverTypeSelect || $serverTypeSelect.length === 0) {
                                $serverTypeSelect = $('#server_type');
                            }
                            if ($serverTypeSelect.length) {
                                $serverTypeSelect.html(response.data);
                                $serverTypeSelect.selectpicker('refresh');
                            }
                        }
                    }
                });
            }
        });
    });

    // Save new server type
    $('#save-servertype').click(function() {
        var url = "{{ route('server-type.store') }}";
        $.easyAjax({
            url: url,
            container: '#createServerTypeForm',
            type: "POST",
            data: $('#createServerTypeForm').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    // Function to update server_type dropdown in any context
                    function updateServerTypeDropdown() {
                        // Try to find in RIGHT_MODAL first, then fallback to global
                        var $serverTypeSelect = null;
                        if (typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                            $serverTypeSelect = $(RIGHT_MODAL_CONTENT).find('#server_type');
                        }
                        if (!$serverTypeSelect || $serverTypeSelect.length === 0) {
                            $serverTypeSelect = $('#server_type');
                        }

                        if ($serverTypeSelect.length) {
                            $serverTypeSelect.html(response.data);
                            $serverTypeSelect.selectpicker('refresh');
                            return true;
                        }
                        return false;
                    }

                    // Update immediately
                    updateServerTypeDropdown();

                    // Close MODAL_LG
                    $(MODAL_LG).modal('hide');

                    // Also try to update after modal is fully hidden (for RIGHT_MODAL context)
                    $(MODAL_LG).on('hidden.bs.modal', function() {
                        setTimeout(function() {
                            updateServerTypeDropdown();
                        }, 100);
                        $(MODAL_LG).off('hidden.bs.modal');
                    });
                }
            }
        })
    });

    // Inline editing
    $('[contenteditable=true]').focus(function() {
        $(this).data("initialText", $(this).html());
    }).blur(function() {
        if ($(this).data("initialText") !== $(this).html()) {
            let id = $(this).data('row-id');
            let tableId = $(this).parent().attr('id');

            if (id) {
                var url = "{{ route('server-type.update', ':id') }}";
                url = url.replace(':id', id);
                var token = "{{ csrf_token() }}";
                // Get selected value from either RIGHT_MODAL or regular context
                var $serverTypeSelect = null;
                if (typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                    $serverTypeSelect = $(RIGHT_MODAL_CONTENT).find('#server_type');
                }
                if (!$serverTypeSelect || $serverTypeSelect.length === 0) {
                    $serverTypeSelect = $('#server_type');
                }
                let selectedServerType = $serverTypeSelect.length ? $serverTypeSelect.val() : null;

                $('#' + tableId).each(function() {
                    let name = $(this).find("td:nth-child(2)").html();
                    let description = $(this).find("td:nth-child(3)").html();

                    $.easyAjax({
                        url: url,
                        container: '#servertype-' + id,
                        type: "POST",
                        data: {
                            'name': name,
                            'description': description,
                            '_token': token,
                            '_method': 'PUT'
                        },
                        blockUI: true,
                        success: function(response) {
                            if (response.status == 'success') {
                                // Find server_type dropdown in both RIGHT_MODAL and regular page contexts
                                var $serverTypeSelect = null;
                                if (typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                                    $serverTypeSelect = $(RIGHT_MODAL_CONTENT).find('#server_type');
                                }
                                if (!$serverTypeSelect || $serverTypeSelect.length === 0) {
                                    $serverTypeSelect = $('#server_type');
                                }
                                if ($serverTypeSelect.length) {
                                    $serverTypeSelect.html(response.data);
                                    $serverTypeSelect.val(selectedServerType);
                                    $serverTypeSelect.selectpicker('refresh');
                                }
                            }
                        }
                    });
                });
            }
        }
    });

    // Status change
    $('.change-status').on('change', function() {
        var id = $(this).data('servertype-id');
        var status = $(this).val();

        if (id) {
            var url = "{{ route('server-type.update', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#servertype-' + id,
                type: "POST",
                data: {
                    'status': status,
                    '_token': token,
                    '_method': 'PUT'
                },
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        // Find server_type dropdown in both RIGHT_MODAL and regular page contexts
                        var $serverTypeSelect = null;
                        if (typeof RIGHT_MODAL_CONTENT !== 'undefined') {
                            $serverTypeSelect = $(RIGHT_MODAL_CONTENT).find('#server_type');
                        }
                        if (!$serverTypeSelect || $serverTypeSelect.length === 0) {
                            $serverTypeSelect = $('#server_type');
                        }
                        if ($serverTypeSelect.length) {
                            $serverTypeSelect.html(response.data);
                            $serverTypeSelect.selectpicker('refresh');
                        }
                    }
                }
            });
        }
    });
</script>


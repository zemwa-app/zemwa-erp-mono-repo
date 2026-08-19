<div class="content-wrapper border-top-0 client-detail-wrapper">
    <div class="card bg-white rounded">
        <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom">
            @lang('letter::app.letterDetails')

            <span class="float-right">
                <button id="downloadButton" class="btn btn-primary py-1 px-2 f-14">
                    @lang('letter::app.download')
                </button>

                <button id="printButton" class="btn btn-success  py-1 px-2 f-14">
                    @lang('letter::app.print')
                </button>
            </span>
        </h4>

        <div class="col-md-12 p-0 text-wrap ql-editor" id="descriptionPreviewArea"
            style="overflow-y: auto; overflow-x: hidden;">
            <div @style(['padding-top:' . $letter->top . 'px', 'padding-bottom:' . $letter->bottom . 'px', 'padding-left:' . $letter->left . 'px', 'padding-right:' . $letter->right . 'px']) id="descriptionPreview">{!! $description !!}</div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#downloadButton').on('click', function() {
            window.location.href = "{{ route('letter.download', $letter->id) }}";
        });

        init(RIGHT_MODAL);
    });
</script>

@include('letter::letter.partials.print-script')

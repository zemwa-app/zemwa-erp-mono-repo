@php
//dd($selectedAssessment->assessment_type);
@endphp

<div class="row my-2">
    <div class="col-sm-10 d-flex align-items-center">
    </div>
    <div class="col-sm-2">
        <a type="button" class="btn btn-block btn-outline-success btn-sm f-14 p-2 mr-3 openRightModal" href="{{ route('publicassessmentpro.config.createQuestion', ['id' => $selectedAssessment->id]) }}"><i class="fa fa-plus mr-1"></i> Add Q&A</a>
    </div>
</div>

@if(count($pasAssessQuestion) != 0)
<table class="table">
    <thead class="">
    <tr>
        <th scope="col" class="col-sm-1">#</th>
        <th scope="col" class="col-sm-4">Assessment Question</th>
        @if($selectedAssessment->assessment_type<2)
        <th scope="col" class="col-sm-4">Options (with correct answer)</th>
        <th scope="col" class="col-sm-1 text-center">Score</th>
        @endif
        <th scope="col" class="col-sm-1 text-center">Status</th>
        <th scope="col" class="col-sm-1"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($pasAssessQuestion as $index => $paQuestion)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td><strong>{{ $paQuestion->question }}</strong></td>
        @if($selectedAssessment->assessment_type<2)
        <td>
            <ul>
                @foreach($paQuestion->answers as $index => $paAnswer)
                <li class="@if($paAnswer->ans_code == $paQuestion->correct_answer)font-weight-bold text-success @endif">
                    @if($paAnswer->ans_code == $paQuestion->correct_answer)
                    &#10003;
                    @else
                    {{ ($index+1) }}
                    @endif
                    {{ $paAnswer->answer }}
                </li>
                @endforeach
            </ul>
        </td>
        <td class="text-center">{{ $paQuestion->score ? $paQuestion->score :"Not Defined" }}</td>
        @endif
        <td class="text-center"><i class="fas fa-circle {{ $paQuestion->status ? 'text-success' : 'text-danger' }}"></i></td>
        <td class="d-flex justify-content-end px-2">
            <a class="btn btn-outline-danger btn-sm mx-2 openRightModal" href="{{ route('publicassessmentpro.config.editQa', ['aid' => $selectedAssessment->id, 'qid' => $paQuestion->id]) }}"><i class="fa fa-edit"></i></a>
            <button type="button" data-id="{{ $paQuestion->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteQuestion" data-toggle="tooltip" data-placement="top" title="Delete Question"><i class="fa fa-trash"></i></button>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
<div class="alert alert-warning" role="alert">
    <i class="fa fa-info mx-2"></i> @lang('publicassessmentpro::app.message.noDataFound')
</div>
@endif

<script>
    $('body').on('click', '.deleteQuestion', function() {
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
                var id = $(this).data('id');
                var url = '{{ route("publicassessmentpro.config.destroyQa", ['id' => 'placeholder']) }}'.replace('placeholder', id);
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
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });
</script>

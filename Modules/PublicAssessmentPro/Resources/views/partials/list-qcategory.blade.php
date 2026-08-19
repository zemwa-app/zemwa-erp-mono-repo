@php

@endphp

@foreach($paCategories as $index => $paCategory)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>
        <strong>{{ $paCategory->category_name }}</strong><br>
    </td>
    <td class="d-flex justify-content-end px-2">
        <button type="button" data-id="{{ $paCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteCategory"><i class="fa fa-trash"></i></button>
    </td>
</tr>
@endforeach

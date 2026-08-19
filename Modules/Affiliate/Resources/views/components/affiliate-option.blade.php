@php
$content = "<div class='d-flex align-items-center text-left'>
    <div class='taskEmployeeImg border-0 d-inline-block mr-1'>
        <img class='rounded-circle' src='".$user->image_url."'>
    </div>
    <div>". htmlentities($user->userBadge());

        if (isset($additionalText) && !is_null($additionalText)) {
        $content .= "<div class='f-10 font-weight-light my-1'>".$additionalText."</div>";
        }

        $content.="</div>";

    @endphp

    <option @selected($selected) data-content="{!! $content !!}" value="{{ $affiliateId }}">
        {{ $user->name_salutation }}
    </option>

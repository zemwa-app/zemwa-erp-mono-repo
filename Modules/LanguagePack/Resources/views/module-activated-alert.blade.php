@if(session('languagepack_module_activated') == 'activated')
@php
    $url = \Illuminate\Support\Facades\Route::has('language-settings.index') 
        ? route('language-settings.index') 
        : url('account/settings/language-settings');
    $moduleLink = '<a href="'.$url.'" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">'.__('modules.settings.languageSettings').'</a>';
@endphp
<div class="p-4 mb-4 text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
    <div class="flex items-center">
        <span class="font-medium">Note:</span>
        <span class="ml-2">@lang('languagepack::messages.moduleActivatedNote', ['link' => $moduleLink])</span>
    </div>
</div>
@endif

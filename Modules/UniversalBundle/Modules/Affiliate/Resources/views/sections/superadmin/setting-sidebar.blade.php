
@php
    $manageSettingPermission = user()->permission('manage_affiliate_settings');
@endphp

    <x-setting-menu-item :active="$activeMenu" menu="affiliate_settings" :href="route('affiliate-settings.index')"
        :text="__('affiliate::app.menu.affiliateSettings')"/>

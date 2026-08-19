<?php

namespace App\Models;

/**
 * App\Models\MobileAppDownloadSetting
 *
 * @property int $id
 * @property string|null $ios_app_download_url
 * @property string|null $android_app_download_url
 * @property string|null $ios_download_button_text
 * @property string|null $android_download_button_text
 * @property string|null $custom_logo_apps_url
 * @property string|null $custom_logo_apps_button_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class MobileAppDownloadSetting extends BaseModel
{
    public const DEFAULT_APP_IOS = 'https://apps.apple.com/us/app/worksuite/id1473933050';

    public const DEFAULT_APP_ANDROID = 'https://play.google.com/store/apps/details?id=com.froiden.worksuite';

    protected $guarded = ['id'];

    protected $table = 'mobile_app_download_settings';

    public static function instance(): self
    {
        $row = static::query()->first();

        if ($row !== null) {
            return $row;
        }

        return static::query()->create([
            'app_ios' => self::DEFAULT_APP_IOS,
            'app_android' => self::DEFAULT_APP_ANDROID,
        ]);
    }
}

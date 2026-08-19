<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Support\Facades\Storage;
use Modules\RestAPI\Entities\Device;
use Modules\RestAPI\Entities\RestAPISetting;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Http\Requests\RestAPISetting\SendPushRequest;
use Modules\RestAPI\Http\Requests\RestAPISetting\UpdateRequest;
use Modules\RestAPI\Services\FirebaseNotificationService;

class RestAPISettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'restapi::app.menu.restAPISettings';
        $this->activeSettingMenu = 'rest_api_setting';
        $this->middleware(function ($request, $next) {
            abort_403(!user()->is_superadmin && !in_array('restapi', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        $this->restAPISetting = RestAPISetting::first();
        $this->firebaseJsonFileName = null;

        if (!empty($this->restAPISetting->fcm_service_account_file)) {
            $this->firebaseJsonFileName = $this->restAPISetting->fcm_service_account_file;
        }

        return view('restapi::setting.index', $this->data);
    }

    public function firebaseJsonPreview()
    {
        $setting = RestAPISetting::first();

        if (empty($setting?->fcm_service_account_file)) {
            return response()->json([
                'status' => 'fail',
                'message' => __('restapi::app.firebaseJsonNotFound'),
            ], 404);
        }

        $filePath = 'firebase/' . $setting->fcm_service_account_file;
        $disk = Storage::disk(config('filesystems.default'));

        if (!$disk->exists($filePath)) {
            return response()->json([
                'status' => 'fail',
                'message' => __('restapi::app.firebaseJsonNotFound'),
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'content' => $disk->get($filePath),
        ]);
    }

    /**
     * @return array|string[]
     */
    public function update(UpdateRequest $request, $id)
    {
        $restApiSetting = RestAPISetting::find($id);
        $restApiSetting->firebase_enabled = $request->boolean('firebase_enabled');

        if ($request->hasFile('fcm_service_account_file')) {
            if (!empty($restApiSetting->fcm_service_account_file)) {
                Files::deleteFile($restApiSetting->fcm_service_account_file, 'firebase');
            }

            $restApiSetting->fcm_service_account_file = Files::uploadLocalOrS3($request->fcm_service_account_file, 'firebase');
        }

        $restApiSetting->save();

        return Reply::redirect(route('rest-api-setting.index'), __('messages.updateSuccess'));
    }

    public function testPush($platform)
    {
        $this->platform = $platform;
        $this->devices = Device::with('user')
            ->where('user_id', $this->user->id)
            ->where('type', $platform)
            ->latest('id', 'desc')
            ->get();

        return view('restapi::setting.test-push', $this->data);
    }

    /**
     * @return array
     */
    public function sendPush(SendPushRequest $request)
    {
        $notification = [
            'title' => 'Test notification',
            'body' => 'This is test push notification',
            'sound' => 'default',
            'badge' => 1,
            'type' => 'test',
            'platform' => $request->platform,
        ];

        $firebase = app(FirebaseNotificationService::class);

        if (!$firebase->isConfigured()) {
            return Reply::error(__('restapi::app.firebaseConfigMissing'));
        }

        $sent = $firebase->sendToTokens(
            [$request->device_id],
            $notification['title'],
            $notification['body'],
            $notification
        );

        if (!$sent) {
            return Reply::error(__('restapi::app.notificationSendFailed'));
        }

        return Reply::success(__('restapi::app.notificationSentSuccessfully'));
    }

}

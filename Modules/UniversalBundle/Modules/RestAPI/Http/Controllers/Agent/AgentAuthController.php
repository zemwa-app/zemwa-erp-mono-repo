<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Models\EmployeeDetails;
use App\Models\UserAuth;
use App\Models\StorageSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\RestAPI\Entities\AgentEvent;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Http\Requests\Agent\LoginRequest;

class AgentAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {

        $userAuth = UserAuth::where('email', $request->email)->first();

        if (!$userAuth || !Hash::check($request->password, $userAuth->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $user = User::where('user_auth_id', $userAuth->id)->first();

        if (!$user || $user->status === 'deactive') {
            return response()->json(['message' => 'Account is disabled'], 403);
        }

        $expiry = now()->addDays(30);
        $tokenName = 'desktop-agent-' . Str::slug($user->name);
        $token = $user->createToken($tokenName, ['agent'], $expiry)->plainTextToken;

        $employeeDetail = EmployeeDetails::where('user_id', $user->id)->first();
        $company = $user->company;

        $storageSetting = StorageSetting::where('status', 'enabled')->firstOrFail();

        return response()->json([
            'user_auth_id' => $userAuth->id,
            'company_id' => $user->company_id,
            'token' => $token,
            'employee_id' => $employeeDetail?->employee_id ?? ('E' . str_pad($user->id, 3, '0', STR_PAD_LEFT)),
            'org_id' => $company?->hash ?? ('ORG' . $user->company_id),
            'employee_name' => $user->name,
            'org_name' => $company?->company_name ?? '',
            'server_url' => request()->getSchemeAndHttpHost(),
            'company' => $company->toArray(),
            'storage_setting' => $storageSetting->toArray(),
            'monitoring_enabled' => $employeeDetail?->monitoring_enabled ?? false,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        AgentEvent::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'session_ended',
            'payload' => ['timestamp' => now()->toIso8601String()],
        ]);

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

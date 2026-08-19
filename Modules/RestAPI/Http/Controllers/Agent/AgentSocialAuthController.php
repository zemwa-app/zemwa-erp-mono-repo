<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Models\EmployeeDetails;
use App\Models\Social;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\UserAuth;
use App\Traits\SocialAuthSettings;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\RestAPI\Entities\User;

class AgentSocialAuthController extends Controller
{
    use SocialAuthSettings;

    private const PROVIDERS = [
        'google' => ['driver' => 'google', 'status' => 'google_status'],
        'facebook' => ['driver' => 'facebook', 'status' => 'facebook_status'],
        'twitter' => ['driver' => 'twitter-oauth-2', 'status' => 'twitter_status'],
        'linkedin' => ['driver' => 'linkedin-openid', 'status' => 'linkedin_status'],
    ];

    public function settings(): JsonResponse
    {
        $settings = SocialAuthSetting::first();

        if (!$settings || !$settings->social_auth_enable) {
            return response()->json([
                'enabled' => false,
                'providers' => [],
            ]);
        }

        $providers = [];
        foreach (self::PROVIDERS as $id => $meta) {
            if ($settings->{$meta['status']} === 'enable') {
                $providers[] = $id;
            }
        }

        return response()->json([
            'enabled' => count($providers) > 0,
            'providers' => $providers,
        ]);
    }

    public function redirect(Request $request, string $provider): RedirectResponse|JsonResponse
    {
        if (!$this->providerEnabled($provider)) {
            return response()->json(['message' => 'Social login is not enabled for this provider'], 404);
        }

        $port = (int) $request->query('port', 27183);
        if ($port < 1024 || $port > 65535) {
            return response()->json(['message' => 'Invalid callback port'], 400);
        }

        $state = Str::random(48);
        Cache::put($this->cacheKey($state), ['port' => $port, 'provider' => $provider], now()->addMinutes(10));

        $driver = $this->configureAgentSocialite($provider);

        return Socialite::driver($driver)
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $state = (string) $request->query('state', '');
        $session = $state !== '' ? Cache::pull($this->cacheKey($state)) : null;
        $port = is_array($session) ? (int) ($session['port'] ?? 27183) : 27183;

        if (!$this->providerEnabled($provider)) {
            return $this->redirectToAgent($port, ['error' => 'Social login is not enabled']);
        }

        try {
            $driver = $this->configureAgentSocialite($provider);
            $data = $this->fetchSocialUser($driver, $provider);
            $userAuth = $this->resolveUserAuth($data, $provider);

            if (!$userAuth) {
                return $this->redirectToAgent($port, ['error' => 'No account found for this social login. Sign in on the web app first.']);
            }

            if ($userAuth->status === 'deactive') {
                return $this->redirectToAgent($port, ['error' => 'Account is disabled']);
            }

            if ($userAuth->login === 'disable') {
                return $this->redirectToAgent($port, ['error' => 'Login is disabled for this account']);
            }

            $user = User::where('user_auth_id', $userAuth->id)->first();
            if (!$user || $user->status === 'deactive') {
                return $this->redirectToAgent($port, ['error' => 'Account is disabled']);
            }

            DB::beginTransaction();
            Social::updateOrCreate(['user_id' => $userAuth->id], [
                'social_id' => $data->getId(),
                'social_service' => $provider,
            ]);
            DB::commit();

            $payload = $this->buildAgentTokenPayload($user, $userAuth);

            return $this->redirectToAgent($port, $payload);
        } catch (Exception $e) {
            return $this->redirectToAgent($port, ['error' => $e->getMessage()]);
        }
    }

    private function providerEnabled(string $provider): bool
    {
        if (!isset(self::PROVIDERS[$provider])) {
            return false;
        }

        $settings = SocialAuthSetting::first();
        if (!$settings || !$settings->social_auth_enable) {
            return false;
        }

        return $settings->{self::PROVIDERS[$provider]['status']} === 'enable';
    }

    private function configureAgentSocialite(string $provider): string
    {
        $this->setSocailAuthConfigs();
        $driver = self::PROVIDERS[$provider]['driver'];
        $callback = route('api.agent.social.callback', ['provider' => $provider]);
        Config::set('services.' . $driver . '.redirect', $callback);

        return $driver;
    }

    private function fetchSocialUser(string $driver, string $provider)
    {
        if ($provider === 'twitter') {
            return Socialite::driver('twitter-oauth-2')->stateless()->user();
        }

        if ($provider === 'linkedin') {
            return Socialite::driver('linkedin-openid')->stateless()->user();
        }

        return Socialite::driver($driver)->stateless()->user();
    }

    private function resolveUserAuth($data, string $provider): ?UserAuth
    {
        if ($provider === 'twitter') {
            return UserAuth::where('twitter_id', $data->getId())->first();
        }

        return UserAuth::where('email', $data->getEmail())->first();
    }

    private function buildAgentTokenPayload(User $user, UserAuth $userAuth): array
    {
        $expiry = now()->addDays(30);
        $tokenName = 'desktop-agent-' . Str::slug($user->name);
        $token = $user->createToken($tokenName, ['agent'], $expiry)->plainTextToken;

        $employeeDetail = EmployeeDetails::where('user_id', $user->id)->first();
        $company = $user->company;
        StorageSetting::where('status', 'enabled')->firstOrFail();

        return [
            'token' => $token,
            'employee_id' => $employeeDetail?->employee_id ?? ('E' . str_pad($user->id, 3, '0', STR_PAD_LEFT)),
            'org_id' => $company?->hash ?? ('ORG' . $user->company_id),
            'employee_name' => $user->name,
            'org_name' => $company?->company_name ?? '',
            'company_id' => (string) $user->company_id,
            'monitoring_enabled' => $employeeDetail?->monitoring_enabled,
        ];
    }

    private function cacheKey(string $state): string
    {
        return 'agent_oauth:' . $state;
    }

    private function redirectToAgent(int $port, array $params): RedirectResponse
    {
        $query = http_build_query($params);
        return redirect()->away('http://127.0.0.1:' . $port . '/callback?' . $query);
    }
}

<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Modules\RestAPI\Entities\User;
use App\Models\User as AppUser;
use Froiden\RestAPI\ApiResponse;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RestAPI\Http\Requests\Auth\EmailVerifyRequest;
use Modules\RestAPI\Http\Requests\Auth\LoginRequest;
use Modules\RestAPI\Http\Requests\Auth\RefreshTokenRequest;

class AuthController extends ApiBaseController
{
    private function withPermissionTypePivot($user)
    {
        $user->load([
            'roles' => function ($query) use ($user) {
                $query->withoutGlobalScope(CompanyScope::class)
                    ->where('roles.company_id', $user->company_id)
                    ->select('roles.id', 'roles.name', 'roles.display_name');
            },
            'roles.permissions' => function ($query) {
                $query->select('permissions.id', 'permissions.name', 'permissions.display_name', 'permissions.allowed_permissions');
            },
        ]);

        $roleName = $user->getUserOtherRoleAttribute();

        // Filter roles if specific role required
        if ($roleName) {
            $user->setRelation(
                'roles',
                $user->roles->where('name', $roleName)->values()
            );
        }

        $roleIds = $user->roles->pluck('id')->all();
        // Now $roleData will be either the specific role or all user roles
        if (empty($roleIds)) {
            return $user;
        }

        $permissionTypeMap = DB::table('permission_role')
            ->whereIn('role_id', $roleIds)
            ->select('role_id', 'permission_id', 'permission_type_id')
            ->get()
            ->keyBy(fn($row) => $row->role_id . '-' . $row->permission_id);

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $key = $role->id . '-' . $permission->id;
                $permission->pivot->permission_type_id = $permissionTypeMap[$key]->permission_type_id ?? null;
                $permission->pivot->makeHidden([
                    'role_id',
                    'permission_id',
                ]);
            }
        }

        return $user;
    }

    public function login(LoginRequest $request)
    {
        // Modifications to this function may also require modifications to
        $email = $request->get('email');
        $password = $request->get('password');
        $days = 365;
        $minutes = 60 * 60 * $days;
        $claims = ['exp' => (int)now()->addYear()->getTimestamp(), 'remember' => 1, 'type' => 1];

        $check = auth()->attempt(['email' => $email, 'password' => $password]);

        if ($check) {
            $user = auth()->user()->user;

            if ($user && $user->status === 'deactive') {
                auth()->logout();
                $exception = new ApiException('User account disabled', null, 403, 403, 2015);

                return ApiResponse::exception($exception);
            }

            $expiry = now()->addYear();
            $tokenName = Str::slug($user->name . ' ' . $user->id);

            $token = auth()->user()->createToken($tokenName, ['*'], $expiry, $claims)->plainTextToken;


            if (isWorksuiteSaas() && $user->is_superadmin) {
                $exception = new \Froiden\RestAPI\Exceptions\UnauthorizedException('Sorry this app is not built for superadmin', null, 403, 403, 2006);

                return \Froiden\RestAPI\ApiResponse::exception($exception);
            }

            // i want only companuy id , name of company and company logo
            $userCompanies = $this->getUserCompanies($user);
       


            return ApiResponse::make('Logged in successfully', [
                'token' => $token,
                'user_companies' => $userCompanies,
                'user' => $this->withPermissionTypePivot($user),
                'expires' => $expiry->format('Y-m-d\TH:i:sP'),
                'expires_in' => $minutes,
            ]);
        }

        $exception = new ApiException('Wrong credentials provided', null, 403, 403, 2001);

        return ApiResponse::exception($exception);
    }

    public function companyLogin(Request $request)
    {
        $authenticatedUser = auth()->user();

        if (!$authenticatedUser) {
            return ApiResponse::exception(new ApiException('Unauthorized user', null, 403, 403, 2001));
        }

        $currentUserAuthId = (int) $authenticatedUser->id;
        $requestUserAuthId = (int) $request->input('user_auth_id');
        $requestCompanyId = (int) $request->input('company_id');

        // Validate request
        if (!$requestUserAuthId || !$requestCompanyId || $currentUserAuthId !== $requestUserAuthId) {
            return ApiResponse::exception(new ApiException('Unauthorized user', null, 403, 403, 2001));
        }

        // Validate company access
        $currentUserCompanies = user_companies($authenticatedUser);
        $currentUserCompanyIds = $currentUserCompanies->pluck('company_id')->map(fn($id) => (int)$id)->toArray();

        if (!in_array($requestCompanyId, $currentUserCompanyIds, true)) {
            return ApiResponse::exception(new ApiException('Unauthorized company', null, 403, 403, 2001));
        }

        // Get workspace user
        $workspaceUser = AppUser::withoutGlobalScopes()
            ->where('user_auth_id', $currentUserAuthId)
            ->where('company_id', $requestCompanyId)
            ->first();

        if (!$workspaceUser) {
            return ApiResponse::exception(new ApiException('User not found', null, 403, 403, 2001));
        }

        if ($workspaceUser->login === 'disable') {
            return ApiResponse::exception(new ApiException('Login is disabled for this workspace', null, 403, 403, 2001));
        }

        if (isWorksuiteSaas() && $workspaceUser->is_superadmin) {
            return ApiResponse::exception(
                new \Froiden\RestAPI\Exceptions\UnauthorizedException(
                    'Sorry this app is not built for superadmin',
                    null,
                    403,
                    403,
                    2006
                )
            );
        }


        if ($authenticatedUser->currentAccessToken()) {
            $authenticatedUser->currentAccessToken()->delete();
        }

        $days = 365;
        $minutes = 60 * 60 * $days;
        $expiry = now()->addYear();

        $claims = [
            'exp' => (int) $expiry->getTimestamp(),
            'remember' => 1,
            'type' => 1,
            'company_id' => $requestCompanyId,
        ];


        $tokenName = Str::slug($workspaceUser->name . ' ' . $workspaceUser->id);

        $token = $authenticatedUser->createToken(
            $tokenName,
            ['*'],
            $expiry,
            $claims
        )->plainTextToken;
    
        return ApiResponse::make('Company switched successfully', [
            'token' => $token,
            'company_id' => $requestCompanyId,
            'user' => $this->withPermissionTypePivot($workspaceUser),
            'expires' => $expiry->format('Y-m-d\TH:i:sP'),
            'expires_in' => $minutes,
        ]);
    }


    public function logout(Request $request)
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return ApiResponse::make('Token invalidated successfully');
    }

    public function refresh(RefreshTokenRequest $request)
    {
        $user = auth()->user();

        if ($user->status === 'inactive') {
            $this->logout();
            throw new ApiException('User account disabled', null, 403, 403, 2015);
        }

        $expiry = now()->addHour();
        $claims = $user->currentAccessToken()->claims;

        $currentToken = $user->currentAccessToken()->id;
        $tokenName = Str::slug($user->name . ' ' . $user->id);

        $newToken = $user->createToken($tokenName, ['*'], now()->addHour(), $claims)->plainTextToken;

        // Revoke Old Token
        $user->tokens()->where('id', $currentToken)->delete();

        return ApiResponse::make('Token refreshed successfully', [
            'token' => $newToken,
            'expires' => $expiry->format('Y-m-d\TH:i:sP'),
            'expires_in' =>  60, // 60 minutes
        ]);
    }

    public function verify(EmailVerifyRequest $request)
    {

        $user = Employee::where('email_verification_token', $request->token)
            ->whereNotNull('email_verification_token')
            ->first();

        if ($user) {
            DB::beginTransaction();

            $user->email_verification_token = null;
            $user->email_verified = 'yes';
            $user->save();

            $user->company->company_email_verified = 'yes';
            $user->company->save();

            event(new EmailVerificationSuccessEvent($user->company, $user));
            DB::commit();

            return ApiResponse::make('Success', ['status' => 'success']);
        }

        return ApiResponse::make('Token is expired', ['status' => 'fail']);
    }


    public function me(): \Illuminate\Http\Response
    {
        $user = api_user();

        if (!$user) {
            return ApiResponse::exception(new ApiException('Unauthorized user', null, 403, 403, 2001));
        }

        return ApiResponse::make('Auth User', [
            'data' => $this->withPermissionTypePivot($user),
        ]);
    }

    public function getUserCompanies($user)
    {

        $userCompanies = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
            ->where('email', $user->email)
            ->whereHas('approvedCompany')
            ->with('company')
            ->withOut('clientDetails', 'role', 'employeeDetail')
            ->select('id', 'company_id', 'status', 'login')
            ->get();

        $userCompanies = $userCompanies->map(function ($userCompany) {
            
            return [
                'id' => $userCompany->company->id,
                'name' => $userCompany->company->company_name,
                'logo' => $userCompany->company->logo_url,
                'status' => $userCompany->company->status,
                'company_approved' => $userCompany->company->approved ? true : false,
                'user_login' => $userCompany->login,
                'user_status' => $userCompany->status,
            ];
        });

        return $userCompanies;
        
    }
}

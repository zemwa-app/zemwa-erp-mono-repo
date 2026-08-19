<?php

namespace Modules\RestAPI\Entities;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Modules\RestAPI\Entities\Concerns\ParsesFlexibleDateTimes;

class User extends \App\Models\User
{
    use HasApiTokens;
    use ParsesFlexibleDateTimes;

    protected $appends = ['image_url', 'modules', 'name_salutation'];

    /**
     * Sanctum resolves this model as the authenticated user; web guard uses UserAuth.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->getKey();
    }

    protected $default = [
        'id',
        'name',
        'email',
        'status',
    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $filterable = [
        'id',
        'users.name',
        'email',
        'status',
    ];

    public function createToken(string $name, array $abilities = ['*'], \DateTimeInterface $expiresAt = null, array $claims = []): NewAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'claims' => $claims,
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public static function getCacheKey($id)
    {
        return 'user_'.$id;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = \Hash::make($value);
        }
    }

    public function getUserOtherRoleAttribute()
    {
        $userRole = null;

        $nonClientRoles = cache()->remember(
            'non-client-roles',
            now()->addDay(),
            fn() => Role::where('name', '<>', 'client')->orderBy('id')->get()
        );

        foreach ($nonClientRoles as $role) {
            foreach ($this->role as $urole) {
                if ($role->id == $urole->role_id) {
                    $userRole = $role->name;
                }

                if ($userRole == 'admin') {
                    break;
                }
            }
        }

        return $userRole;
    }
}

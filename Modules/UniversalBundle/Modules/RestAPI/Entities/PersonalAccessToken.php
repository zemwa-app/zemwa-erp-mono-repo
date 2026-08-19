<?php

namespace Modules\RestAPI\Entities;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    public function getRelationValue($key)
    {
        if ($key === 'tokenable') {
            if (! array_key_exists('tokenable', $this->relations)) {
                $this->relations['tokenable'] = $this->resolveTokenable();
            }

            return $this->relations['tokenable'];
        }

        return parent::getRelationValue($key);
    }

    protected function resolveTokenable(): ?User
    {
        if ($this->tokenable_type === User::class) {
            return User::find($this->tokenable_id);
        }

        if ($this->tokenable_type === UserAuth::class) {
            return User::where('user_auth_id', $this->tokenable_id)
                ->where('status', 'active')
                ->first();
        }

        if (! class_exists($this->tokenable_type)) {
            return null;
        }

        return $this->morphTo('tokenable', 'tokenable_type', 'tokenable_id')->getResults();
    }


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'claims' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'claims',
        'expires_at',
    ];

}

<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;

class ServerDomainType extends Model
{
    protected $table = 'server_domain_types';

    protected $guarded = ['id'];

    public const DEFAULT_TYPES = [
        'com', 'net', 'org', 'info', 'biz', 'co', 'io', 'me', 'tv', 'app', 'dev',
    ];

    public static function normalizeType(string $type): string
    {
        $type = trim($type);
        $type = ltrim($type, '.');
        $type = strtolower($type);

        return $type;
    }

    public static function displayLabel(string $type): string
    {
        $type = self::normalizeType($type);

        if ($type === 'other') {
            return 'Other';
        }

        return '.' . $type;
    }

    public static function ensureDefaultsForCompany(int $companyId): void
    {
        $existing = static::where('company_id', $companyId)->pluck('type')->map(fn ($t) => self::normalizeType($t))->all();
        $existing = array_fill_keys($existing, true);

        foreach (self::DEFAULT_TYPES as $type) {
            if (isset($existing[$type])) {
                continue;
            }

            static::create([
                'company_id' => $companyId,
                'type' => $type,
                'label' => self::displayLabel($type),
                'is_active' => true,
                'created_by' => user()?->id,
            ]);
        }
    }
}


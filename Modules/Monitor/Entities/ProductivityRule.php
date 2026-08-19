<?php

namespace Modules\Monitor\Entities;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductivityRule extends Model
{

    public const TYPE_URL = 'url';

    public const TYPE_APP = 'app';

    protected $table = 'productivity_rules';

    protected $fillable = [
        'company_id',
        'type',
        'pattern',
        'category',
        'subcategory',
        'priority',
        'match_count',
    ];

    protected $casts = [
        'match_count' => 'integer',
        'priority' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isGlobal(): bool
    {
        return $this->company_id === null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function subcategoriesByCategory(): array
    {
        return config('monitor.productivity.subcategories', []);
    }
}

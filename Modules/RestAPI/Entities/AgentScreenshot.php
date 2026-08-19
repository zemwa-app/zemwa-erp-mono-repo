<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class AgentScreenshot extends Model
{
    protected $table = 'agent_screenshots';

    protected $fillable = [
        'company_id',
        'user_id',
        'task_id',
        'captured_at',
        'file_path',
        'thumbnail_path',
        'active_app',
        'window_title',
        'category',
        'display_idx',
        'is_triggered',
        'file_size',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'is_triggered' => 'boolean',
        'display_idx' => 'integer',
        'file_size' => 'integer',
        'task_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(\App\Models\Task::class, 'task_id');
    }

    public function fullUrl(): string
    {
        return asset_url_local_s3($this->file_path);
    }

    public function thumbnailUrl(): string
    {
        $path = $this->thumbnail_path ?: $this->file_path;

        return asset_url_local_s3($path);
    }
}

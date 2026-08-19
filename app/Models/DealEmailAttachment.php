<?php

namespace App\Models;

use App\Helper\Files;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DealEmailAttachment extends BaseModel
{
    const FILE_PATH = 'deal-email-attachments';

    protected $guarded = ['id'];

    public function history(): BelongsTo
    {
        return $this->belongsTo(DealEmailHistory::class, 'deal_email_history_id');
    }

    public function getStoragePath(): string
    {
        return self::FILE_PATH . '/' . $this->deal_email_history_id . '/' . $this->hashname;
    }

    public function getFileContents(): ?string
    {
        $path = $this->getStoragePath();

        if (Storage::disk(config('filesystems.default'))->exists($path)) {
            return Storage::disk(config('filesystems.default'))->get($path);
        }

        $localPath = public_path(Files::UPLOAD_FOLDER . '/' . $path);

        if (file_exists($localPath)) {
            return file_get_contents($localPath);
        }

        return null;
    }
}

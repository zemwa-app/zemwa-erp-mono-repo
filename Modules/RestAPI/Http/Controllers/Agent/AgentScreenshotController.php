<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Helper\Files;
use App\Models\FileStorage;
use App\Models\StorageSetting;
use App\Models\Task;
use App\Scopes\CompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\RestAPI\Entities\AgentScreenshot;
use Modules\RestAPI\Http\Controllers\Agent\Concerns\EnsuresEmployeeMonitoringEnabled;
use Modules\RestAPI\Http\Requests\Agent\ScreenshotRequest;

class AgentScreenshotController extends Controller
{
    use EnsuresEmployeeMonitoringEnabled;

    public function store(ScreenshotRequest $request): JsonResponse
    {
        if ($response = $this->ensureMonitoringEnabled($request)) {
            return $response;
        }

        $raw = $request->input('metadata');
        $metadata = is_array($raw) ? $raw : json_decode($raw, true) ?? [];
        $user = $request->user();

        $date = now()->format('Y-m-d');
        $companyId = $user->company_id;
        $userId = $user->id;

        $file = $request->file('file');
        $storagePath = "monitor-screenshots/{$companyId}/{$date}/{$userId}";

        $filename = Files::uploadLocalOrS3($file, $storagePath);
        $filePath = "{$storagePath}/{$filename}";

        $thumbnailPath = $this->generateThumbnail($file, $storagePath, $filename);

        $taskId = $metadata['task_id'] ?? null;
        if ($taskId) {
            $task = Task::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $companyId)
                ->find($taskId);
            $taskId = $task?->id;
        }

        $screenshot = AgentScreenshot::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'task_id' => $taskId,
            'captured_at' => $metadata['timestamp'] ?? now(),
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'active_app' => $metadata['active_app'] ?? null,
            'window_title' => $metadata['window_title'] ?? null,
            'category' => $metadata['category'] ?? 'neutral',
            'display_idx' => $metadata['display_idx'] ?? 0,
            'is_triggered' => $metadata['is_triggered'] ?? false,
            'file_size' => $metadata['file_size'] ?? $file->getSize(),
        ]);

        return response()->json([
            'id' => $screenshot->id,
            'task_id' => $screenshot->task_id,
            'url' => asset_url_local_s3($filePath),
            'thumbnail_url' => $thumbnailPath ? asset_url_local_s3($thumbnailPath) : null,
        ], 201);
    }

    private function generateThumbnail($file, string $storagePath, string $filename): ?string
    {
        try {
            $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
            $thumbPath = "{$storagePath}/{$thumbFilename}";

            if (class_exists(\Intervention\Image\Facades\Image::class)) {
                $image = \Intervention\Image\Facades\Image::make($file->getRealPath());
                $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $encoded = (string) $image->encode('jpg', 60);

                $fileVisibility = [];

                if (config('filesystems.default') == 'local') {
                    $fileVisibility = ['directory_visibility' => 'public', 'visibility' => 'public'];
                }

                Storage::disk(config('filesystems.default'))->put($thumbPath, $encoded, $fileVisibility);

                $setting = StorageSetting::where('status', 'enabled')->firstOrFail();
                $fileStorage = new FileStorage();
                $fileStorage->filename = $thumbFilename;
                $fileStorage->size = strlen($encoded);
                $fileStorage->type = 'image/jpeg';
                $fileStorage->path = $storagePath;
                $fileStorage->storage_location = $setting->filesystem;
                $fileStorage->save();

                return $thumbPath;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

<?php

namespace Modules\Monitor\Services;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MonitorInstallerService
{
    private const MANIFEST_FILE = 'manifest.json';

    private const PLATFORM_KEYS = ['windows', 'mac', 'ubuntu'];

    /**
     * @return array<string, mixed>
     */
    public function getPageData(bool $includeManageMeta = false): array
    {
        if ($this->isDemo()) {
            return [
                'version' => config('monitor.installer.version', '1.0.0'),
                'released_at' => $this->formatReleasedAtForInput(config('monitor.installer.released_at')),
                'released_at_label' => $this->formatReleasedAtLabel(config('monitor.installer.released_at')),
                'platforms' => $this->getPlatformDownloads($includeManageMeta),
                'can_manage' => false,
            ];
        }

        $manifest = $this->getManifest();

        return [
            'version' => $manifest['version'] ?? config('monitor.installer.version', '1.0.0'),
            'released_at' => $this->formatReleasedAtForInput($manifest['released_at'] ?? config('monitor.installer.released_at')),
            'released_at_label' => $this->formatReleasedAtLabel($manifest['released_at'] ?? config('monitor.installer.released_at')),
            'platforms' => $this->getPlatformDownloads($includeManageMeta),
            'can_manage' => $this->canManage(),
        ];
    }

    public function canManage(): bool
    {
        if ($this->isDemo()) {
            return false;
        }

        return user() && user()->is_superadmin;
    }

    public function authorizeManage(): void
    {
        abort_403(!$this->canManage());
    }

    /**
     * True when at least one platform installer file or URL is available to download.
     */
    public function hasAvailableInstaller(): bool
    {
        foreach (array_keys(config('monitor.installer.platforms', [])) as $platform) {
            if ($this->isPlatformAvailable($platform)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPlatformDownloads(bool $includeManageMeta = false): array
    {
        $manifest = $this->isDemo() ? [] : $this->getManifest();
        $platforms = [];

        foreach (config('monitor.installer.platforms', []) as $key => $platform) {
            $meta = $this->isDemo() ? null : ($manifest['platforms'][$key] ?? null);
            $sourceType = $this->resolveSourceType($meta);
            $externalUrl = $sourceType === 'url' ? ($meta['download_url'] ?? null) : null;
            $path = $this->resolveInstallerPath($key);
            $fileUploaded = !$this->isDemo()
                && $sourceType === 'upload'
                && $path
                && File::exists($path)
                && !empty($meta);
            $configured = $sourceType === 'url'
                ? ($externalUrl !== null && $externalUrl !== '')
                : $fileUploaded;

            if ($this->isDemo()) {
                $filename = $platform['filename'] ?? '';
            } elseif ($sourceType === 'url') {
                $filename = $meta['original_name'] ?? basename(parse_url($externalUrl ?? '', PHP_URL_PATH) ?: '') ?: ($platform['filename'] ?? '');
            } elseif (!empty($meta['original_name'])) {
                $filename = $meta['original_name'];
            } else {
                $filename = $platform['filename'] ?? '';
            }

            $item = [
                'key' => $key,
                'label' => $platform['label'] ?? ucfirst($key),
                'filename' => $filename,
                'available' => $this->isPlatformAvailable($key),
                'uploaded' => $configured,
                'company_uploaded' => $configured,
                'source_type' => $sourceType,
                'external_url' => $externalUrl,
                'download_url' => route('monitor.installer.download', $key),
                'size_label' => $sourceType === 'url'
                    ? ($configured ? __('monitor::app.installerExternalLink') : null)
                    : (($path && File::exists($path)) ? $this->formatBytes(File::size($path)) : null),
                'uploaded_at' => $this->isDemo() ? null : $this->formatUploadedAt($meta['uploaded_at'] ?? null),
                'extension' => $platform['extension'] ?? '',
                'icon' => $platform['icon'] ?? 'fa fa-download',
                'icon_bg' => $platform['icon_bg'] ?? 'bg-gray-100',
                'icon_color' => $platform['icon_color'] ?? 'text-gray-600',
            ];

            if ($includeManageMeta) {
                $item['destroy_url'] = route('monitor.installer-settings.destroy', $key);
            }

            $platforms[] = $item;
        }

        return $platforms;
    }

    public function getPublicInstallerPath(string $platform): ?string
    {
        $filename = config("monitor.installer.platforms.{$platform}.filename");

        if (!$filename) {
            return null;
        }

        return $this->resolvePublicInstallerPath($filename);
    }

    public function servePublicInstaller(string $filename): BinaryFileResponse
    {
        $platform = $this->resolvePlatformForFilename($filename);

        abort_unless($platform !== null, 404);

        $path = $this->getPublicInstallerPath($platform);

        abort_unless($path !== null, 404, __('monitor::app.installerNotAvailable'));

        $meta = config("monitor.installer.platforms.{$platform}", []);

        return response()->download($path, $filename, [
            'Content-Type' => $meta['mime'] ?? 'application/octet-stream',
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function uploadInstallers(Request $request): array
    {
        $this->authorizeManage();

        $rules = [
            'agent_version' => 'nullable|string|max:20',
            'released_at' => 'nullable|date',
        ];

        foreach (self::PLATFORM_KEYS as $platform) {
            $rules[$platform . '_installer'] = 'nullable|file|max:' . $this->maxUploadKilobytes();
            $rules[$platform . '_source_type'] = 'nullable|in:upload,url';

            if ($request->input($platform . '_source_type', 'upload') === 'url') {
                $rules[$platform . '_download_url'] = 'nullable|url|max:2048';
            }
        }

        $request->validate($rules);

        $manifest = $this->getManifest();
        $updated = [];

        if ($request->filled('agent_version')) {
            $manifest['version'] = $request->input('agent_version');
        }

        if ($request->filled('released_at')) {
            $manifest['released_at'] = Carbon::parse($request->input('released_at'))->toDateString();
        }

        foreach (self::PLATFORM_KEYS as $platform) {
            $field = $platform . '_installer';
            $urlField = $platform . '_download_url';
            $sourceType = $request->input($platform . '_source_type', 'upload');

            if ($sourceType === 'upload' && $request->hasFile($field)) {
                $file = $request->file($field);
                $uploadError = $file->getError();

                if ($uploadError !== UPLOAD_ERR_OK) {
                    abort(422, $this->uploadErrorMessage($platform, $uploadError));
                }

                $this->deletePlatformFile($platform);
                $updated[] = $platform;
                $manifest['platforms'][$platform] = $this->storePlatformFile($platform, $file);
                continue;
            }

            if ($sourceType === 'url' && $request->filled($urlField)) {
                $updated[] = $platform;
                $this->deletePlatformFile($platform);
                $manifest['platforms'][$platform] = $this->storePlatformUrl(
                    $platform,
                    $request->input($urlField)
                );
            }
        }

        $hasMetaUpdate = $request->filled('agent_version') || $request->filled('released_at');

        abort_if($updated === [] && !$hasMetaUpdate, 422, __('monitor::app.installerUploadNothing'));

        $this->saveManifest($manifest);

        return $updated;
    }

    public function deleteInstaller(string $platform): void
    {
        $this->authorizeManage();

        abort_unless(in_array($platform, self::PLATFORM_KEYS, true), 404);

        $this->deletePlatformFile($platform);

        $manifest = $this->getManifest();
        unset($manifest['platforms'][$platform]);
        $this->saveManifest($manifest);
    }

    public function download(string $platform): BinaryFileResponse|RedirectResponse
    {
        abort_unless(in_array($platform, self::PLATFORM_KEYS, true), 404);

        if (!$this->isDemo()) {
            $manifest = $this->getManifest();
            $meta = $manifest['platforms'][$platform] ?? null;

            if ($this->resolveSourceType($meta) === 'url' && !empty($meta['download_url'])) {
                return redirect()->away($meta['download_url']);
            }
        }

        $path = $this->resolveInstallerPath($platform);

        abort_unless($path && File::exists($path), 404, __('monitor::app.installerNotAvailable'));

        $meta = config("monitor.installer.platforms.{$platform}", []);

        if ($this->isDemo()) {
            $filename = $meta['filename'] ?? basename($path);
        } else {
            $manifest = $this->getManifest();
            $filename = $manifest['platforms'][$platform]['original_name']
                ?? $meta['filename']
                ?? basename($path);
        }

        return response()->download($path, $filename, [
            'Content-Type' => $meta['mime'] ?? 'application/octet-stream',
        ]);
    }

    public function resolveInstallerPath(string $platform): ?string
    {
        if ($this->isDemo()) {
            return $this->getPublicInstallerPath($platform);
        }

        $manifest = $this->getManifest();
        $meta = $manifest['platforms'][$platform] ?? null;

        if ($this->resolveSourceType($meta) === 'url') {
            return null;
        }

        $storedPath = $this->getStoredFilePath($platform);

        if ($storedPath && File::exists($storedPath)) {
            return $storedPath;
        }

        return $this->getPublicInstallerPath($platform);
    }

    private function isDemo(): bool
    {
        return app()->environment('demo');
    }

    private function resolvePlatformForFilename(string $filename): ?string
    {
        foreach (config('monitor.installer.platforms', []) as $key => $platform) {
            if (($platform['filename'] ?? '') === $filename) {
                return $key;
            }
        }

        return null;
    }

    private function resolvePublicInstallerPath(string $filename): ?string
    {
        $publicPath = $this->getGlobalDir() . '/' . $filename;

        return File::exists($publicPath) ? $publicPath : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function storePlatformFile(string $platform, UploadedFile $file): array
    {
        $config = config("monitor.installer.platforms.{$platform}", []);
        $expectedExt = strtolower($config['extension'] ?? '');
        $clientExt = strtolower($file->getClientOriginalExtension());

        abort_unless($clientExt === $expectedExt, 422, __('monitor::app.installerInvalidExtension', [
            'platform' => $config['label'] ?? $platform,
            'extension' => $expectedExt,
        ]));

        $dir = $this->getGlobalDir();
        File::ensureDirectoryExists($dir);

        $storedName = $config['filename'] ?? ($platform . '.' . $expectedExt);
        $destination = $dir . '/' . $storedName;

        if (File::exists($destination)) {
            File::delete($destination);
        }

        $originalName = $file->getClientOriginalName();
        $file->move($dir, $storedName);

        return [
            'source_type' => 'file',
            'stored_name' => $storedName,
            'original_name' => $originalName,
            'uploaded_at' => now()->toIso8601String(),
            'uploaded_by' => user()->id,
            'size' => File::size($destination),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storePlatformUrl(string $platform, string $url): array
    {
        $config = config("monitor.installer.platforms.{$platform}", []);
        $expectedExt = strtolower($config['extension'] ?? '');
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $urlExt = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_unless(
            $expectedExt === '' || $urlExt === '' || $urlExt === $expectedExt,
            422,
            __('monitor::app.installerInvalidUrlExtension', [
                'platform' => $config['label'] ?? $platform,
                'extension' => $expectedExt,
            ])
        );

        return [
            'source_type' => 'url',
            'download_url' => $url,
            'original_name' => basename($path) ?: ($platform . '.' . $expectedExt),
            'uploaded_at' => now()->toIso8601String(),
            'uploaded_by' => user()->id,
        ];
    }

    private function deletePlatformFile(string $platform): void
    {
        $path = $this->getStoredFilePath($platform);

        if ($path && File::exists($path)) {
            File::delete($path);
        }
    }

    private function isPlatformAvailable(string $platform): bool
    {
        if ($this->isDemo()) {
            $path = $this->getPublicInstallerPath($platform);

            return $path !== null && File::exists($path);
        }

        $manifest = $this->getManifest();
        $meta = $manifest['platforms'][$platform] ?? null;
        $sourceType = $this->resolveSourceType($meta);

        if ($sourceType === 'url') {
            return !empty($meta['download_url']);
        }

        $path = $this->resolveInstallerPath($platform);

        return $path !== null && File::exists($path);
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private function resolveSourceType(?array $meta): string
    {
        if (!$meta) {
            return 'upload';
        }

        if (($meta['source_type'] ?? '') === 'file') {
            return 'upload';
        }

        if (($meta['source_type'] ?? '') === 'url' && !empty($meta['download_url'])) {
            return 'url';
        }

        if (!empty($meta['download_url'])) {
            return 'url';
        }

        if (!empty($meta['stored_name']) || !empty($meta['original_name'])) {
            return 'upload';
        }

        return 'upload';
    }

    private function getStoredFilePath(string $platform): ?string
    {
        $filename = config("monitor.installer.platforms.{$platform}.filename");

        if (!$filename) {
            $manifest = $this->getManifest();
            $filename = $manifest['platforms'][$platform]['stored_name'] ?? null;
        }

        if (!$filename) {
            return null;
        }

        return $this->getGlobalDir() . '/' . $filename;
    }

    private function getGlobalDir(): string
    {
        $relativePath = trim(config('monitor.installer.storage_path', 'monitor/installers'), '/');

        return public_path($relativePath);
    }

    /**
     * @return array<string, mixed>
     */
    private function getManifest(): array
    {
        $path = $this->getGlobalDir() . '/' . self::MANIFEST_FILE;

        if (!File::exists($path)) {
            return [
                'version' => config('monitor.installer.version', '1.0.0'),
                'released_at' => config('monitor.installer.released_at'),
                'platforms' => [],
            ];
        }

        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function saveManifest(array $manifest): void
    {
        $dir = $this->getGlobalDir();
        File::ensureDirectoryExists($dir);
        File::put(
            $dir . '/' . self::MANIFEST_FILE,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function dateTimezone(): string
    {
        if (function_exists('company') && company()) {
            return company()->timezone;
        }

        return global_setting()->timezone ?? config('app.timezone');
    }

    private function dateFormat(): string
    {
        if (function_exists('company') && company()) {
            return company()->date_format;
        }

        return global_setting()->date_format ?? 'Y-m-d';
    }

    private function timeFormat(): string
    {
        if (function_exists('company') && company()) {
            return company()->time_format;
        }

        return global_setting()->time_format ?? 'H:i';
    }

    private function formatReleasedAtForInput(?string $date): string
    {
        if (!$date) {
            return now($this->dateTimezone())->toDateString();
        }

        return Carbon::parse($date)->toDateString();
    }

    private function formatReleasedAtLabel(?string $date): string
    {
        if (!$date) {
            return '—';
        }

        return Carbon::parse($date)
            ->timezone($this->dateTimezone())
            ->format($this->dateFormat());
    }

    private function formatUploadedAt(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)
            ->timezone($this->dateTimezone())
            ->format($this->dateFormat() . ' ' . $this->timeFormat());
    }

    private function maxUploadKilobytes(): int
    {
        return (int) config('monitor.installer.max_upload_mb', 500) * 1024;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    private function uploadErrorMessage(string $platform, int $errorCode): string
    {
        $label = config("monitor.installer.platforms.{$platform}.label", ucfirst($platform));
        $maxMb = (int) config('monitor.installer.max_upload_mb', 500);

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => __('monitor::app.installerUploadPayloadTooLarge', [
                'size' => $maxMb . ' MB',
            ]) . ' (' . $label . ')',
            UPLOAD_ERR_PARTIAL => __('monitor::app.installerUploadNetworkError') . ' (' . $label . ')',
            UPLOAD_ERR_NO_FILE => __('monitor::app.installerUploadNothing'),
            default => __('monitor::app.installerUploadServerError') . ' (' . $label . ')',
        };
    }
}

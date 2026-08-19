<?php

namespace Modules\Monitor\Services\Analytics;

use Illuminate\Support\Facades\Http;

class LogoService
{
    private const CACHE_DIR = 'app-icons/cache';

    private const FAVICON_SIZE = 64;

    /** @var array<int, string> */
    private const AVATAR_COLORS = [
        '#4F46E5', '#0891B2', '#059669', '#D97706', '#DC2626', '#7C3AED', '#DB2777', '#475569',
    ];

    /** @var array<string, string> slug or process fragment => favicon domain */
    private const APP_FAVICON_DOMAINS = [
        'chrome' => 'google.com',
        'firefox' => 'mozilla.org',
        'safari' => 'apple.com',
        'edge' => 'microsoft.com',
        'brave' => 'brave.com',
        'arc' => 'arc.net',
        'opera' => 'opera.com',
        'code' => 'vscode.dev',
        'cursor' => 'cursor.com',
        'warp' => 'warp.dev',
        'iterm2' => 'iterm2.com',
        'terminal' => 'apple.com',
        'hyper' => 'hyper.is',
        'slack' => 'slack.com',
        'teams' => 'microsoft.com',
        'zoom' => 'zoom.us',
        'discord' => 'discord.com',
        'telegram' => 'telegram.org',
        'whatsapp' => 'whatsapp.com',
        'notion' => 'notion.so',
        'obsidian' => 'obsidian.md',
        'figma' => 'figma.com',
        'sketch' => 'sketch.com',
        'postman' => 'postman.com',
        'insomnia' => 'insomnia.rest',
        'docker' => 'docker.com',
        'spotify' => 'spotify.com',
        'vlc' => 'videolan.org',
        'finder' => 'apple.com',
        'systempreferences' => 'apple.com',
        'intellij' => 'jetbrains.com',
        'webstorm' => 'jetbrains.com',
        'phpstorm' => 'jetbrains.com',
        'pycharm' => 'jetbrains.com',
        'goland' => 'jetbrains.com',
        'androidstudio' => 'google.com',
        'xcode' => 'apple.com',
        'excel' => 'microsoft.com',
        'word' => 'microsoft.com',
        'powerpoint' => 'microsoft.com',
        'github-desktop' => 'github.com',
        'sourcetree' => 'sourcetreeapp.com',
        'gitkraken' => 'gitkraken.com',
        'tableplus' => 'tableplus.com',
        'dbeaver' => 'dbeaver.io',
        'linear' => 'linear.app',
        'todoist' => 'todoist.com',
        'jira' => 'atlassian.com',
        'confluence' => 'atlassian.com',
    ];

    /** @var array<string, string> normalised process name => bundled icon slug */
    private const PROCESS_ICON_SLUGS = [
        'com.google.chrome' => 'chrome',
        'com.apple.safari' => 'safari',
        'org.mozilla.firefox' => 'firefox',
        'com.microsoft.edgemac' => 'edge',
        'com.brave.browser' => 'brave',
        'company.thebrowser.browser' => 'arc',
        'com.operasoftware.operastable' => 'opera',
        'com.microsoft.vscode' => 'code',
        'com.todesktop.230313mzl4w4u92' => 'cursor',
        'dev.warp.warp-stable' => 'warp',
        'com.googlecode.iterm2' => 'iterm2',
        'com.apple.terminal' => 'terminal',
        'co.zeit.hyper' => 'hyper',
        'com.tinyspeck.slackmacgap' => 'slack',
        'com.microsoft.teams' => 'teams',
        'us.zoom.xos' => 'zoom',
        'com.hnc.discord' => 'discord',
        'ru.keepcoder.telegram' => 'telegram',
        'com.electron.dockerdesktop' => 'docker',
        'com.spotify.client' => 'spotify',
        'org.videolan.vlc' => 'vlc',
        'com.apple.finder' => 'finder',
        'com.apple.systempreferences' => 'systempreferences',
        'com.jetbrains.intellij' => 'intellij',
        'com.google.android.studio' => 'androidstudio',
        'com.apple.dt.xcode' => 'xcode',
        'com.microsoft.excel' => 'excel',
        'com.microsoft.word' => 'word',
        'com.microsoft.powerpoint' => 'powerpoint',
        'com.github.githubdesktop' => 'github-desktop',
        'com.torusknot.sourcetreemac' => 'sourcetree',
        'com.axosoft.gitkraken' => 'gitkraken',
        'com.tinyapp.tableplus' => 'tableplus',
        'com.dbeaver.dbeaver' => 'dbeaver',
        'com.figma.desktop' => 'figma',
        'notion.id' => 'notion',
        'md.obsidian' => 'obsidian',
        'com.postmanlabs.mac' => 'postman',
        'com.insomnia.app' => 'insomnia',
        'com.linear' => 'linear',
        'com.todoist.mac.todoist' => 'todoist',
    ];

    /**
     * Returns a local asset URL for the domain favicon (downloads once, then reuses cache).
     */
    public function getWebsiteLogo(string $domain): ?string
    {
        $domain = $this->normalizeDomain($domain);

        if ($domain === '') {
            return null;
        }

        return $this->resolveCachedFaviconUrl($domain);
    }

    public function getAppIconUrl(?string $processName, ?string $appName = null): ?string
    {
        $slug = $this->resolveIconSlug($processName, $appName);

        if ($slug !== '' && file_exists(public_path('app-icons/' . $slug . '.png'))) {
            return asset('app-icons/' . $slug . '.png');
        }

        $domain = $this->faviconDomainForSlug($slug, $appName, $processName);

        return $domain ? $this->resolveCachedFaviconUrl($domain) : null;
    }

    /**
     * @return array{letter: string, color: string}
     */
    public function letterAvatarMeta(string $label): array
    {
        $label = trim($label) ?: '?';

        return [
            'letter' => mb_strtoupper(mb_substr($label, 0, 1)),
            'color' => self::AVATAR_COLORS[crc32(mb_strtolower($label)) % count(self::AVATAR_COLORS)],
        ];
    }

    /**
     * @return array{icon_url: ?string, letter_avatar: array{letter: string, color: string}}
     */
    public function resolveForActivityLog(?string $url, ?string $appName, ?string $processName): array
    {
        $label = $this->displayLabel($url, $appName, $processName);
        $domain = $url ? $this->extractDomain($url) : null;

        $iconUrl = $domain
            ? $this->getWebsiteLogo($domain)
            : $this->getAppIconUrl($processName, $appName);

        return [
            'icon_url' => $iconUrl,
            'letter_avatar' => $this->letterAvatarMeta($label),
        ];
    }

    public function letterAvatarDataUri(string $label, int $size = 32): string
    {
        $meta = $this->letterAvatarMeta($label);
        $fontSize = (int) round($size * 0.45);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">'
            . '<rect width="' . $size . '" height="' . $size . '" rx="' . (int) ($size / 2) . '" fill="' . $meta['color'] . '"/>'
            . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="' . $fontSize . '" font-weight="600">'
            . htmlspecialchars($meta['letter'], ENT_XML1)
            . '</text></svg>';

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    public function displayLabel(?string $url, ?string $appName, ?string $processName): string
    {
        if ($url) {
            return $this->normalizeDomain($this->extractDomain($url) ?? $appName ?? 'Website');
        }

        return $appName ?: ($processName ?: 'Unknown');
    }

    public function extractDomain(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (!$host && !str_contains($url, '://')) {
            $host = parse_url('https://' . $url, PHP_URL_HOST);
        }

        return $host ? $this->normalizeDomain($host) : null;
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        return preg_replace('/^www\./', '', $domain) ?? $domain;
    }

    public function processSlug(?string $processName): string
    {
        if (!$processName) {
            return '';
        }

        $slug = strtolower($processName);
        $slug = preg_replace('/\.exe$/i', '', $slug) ?? $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }

    public function resolveIconSlug(?string $processName, ?string $appName = null): string
    {
        if ($processName) {
            $normalised = strtolower(trim($processName));

            if (isset(self::PROCESS_ICON_SLUGS[$normalised])) {
                return self::PROCESS_ICON_SLUGS[$normalised];
            }
        }

        $slug = $this->processSlug($processName);

        if ($slug !== '' && isset(self::APP_FAVICON_DOMAINS[$slug])) {
            return $slug;
        }

        if ($appName) {
            $appSlug = $this->processSlug($appName);

            if ($appSlug !== '' && isset(self::APP_FAVICON_DOMAINS[$appSlug])) {
                return $appSlug;
            }
        }

        return $slug;
    }

    private function resolveCachedFaviconUrl(string $domain): ?string
    {
        $localUrl = $this->localCachedIconUrl($domain);

        if ($localUrl !== null) {
            return $localUrl;
        }

        if ($this->downloadAndCacheFavicon($domain)) {
            return $this->localCachedIconUrl($domain);
        }

        return null;
    }

    private function localCachedIconUrl(string $domain): ?string
    {
        $path = $this->cachedIconPath($domain);

        if (!is_file($path) || filesize($path) < 50) {
            return null;
        }

        return asset(self::CACHE_DIR . '/' . $this->cacheFilename($domain) . '.png');
    }

    private function cachedIconPath(string $domain): string
    {
        return public_path(self::CACHE_DIR . '/' . $this->cacheFilename($domain) . '.png');
    }

    private function cacheFilename(string $domain): string
    {
        $safe = preg_replace('/[^a-z0-9.-]+/', '_', $this->normalizeDomain($domain)) ?? 'unknown';

        return trim($safe, '_') ?: 'unknown';
    }

    private function googleFaviconUrl(string $domain): string
    {
        return 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=' . self::FAVICON_SIZE;
    }

    private function downloadAndCacheFavicon(string $domain): bool
    {
        $path = $this->cachedIconPath($domain);

        if (is_file($path) && filesize($path) >= 50) {
            return true;
        }

        $this->ensureCacheDirectory();

        $lockPath = $path . '.lock';
        $lock = @fopen($lockPath, 'c+');

        if ($lock === false) {
            return is_file($path) && filesize($path) >= 50;
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                return is_file($path) && filesize($path) >= 50;
            }

            if (is_file($path) && filesize($path) >= 50) {
                return true;
            }

            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'MonitoringAgent/1.0'])
                ->get($this->googleFaviconUrl($domain));

            if (!$response->successful()) {
                return false;
            }

            $body = $response->body();

            if (strlen($body) < 50) {
                return false;
            }

            $written = file_put_contents($path, $body);

            return $written !== false && $written >= 50;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockPath);
        }
    }

    private function ensureCacheDirectory(): void
    {
        $dir = public_path(self::CACHE_DIR);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function faviconDomainForSlug(string $slug, ?string $appName, ?string $processName): ?string
    {
        if ($slug !== '' && isset(self::APP_FAVICON_DOMAINS[$slug])) {
            return self::APP_FAVICON_DOMAINS[$slug];
        }

        $haystack = strtolower(trim(($appName ?? '') . ' ' . ($processName ?? '')));

        foreach (self::APP_FAVICON_DOMAINS as $key => $domain) {
            if (str_contains($haystack, str_replace('-', ' ', $key)) || str_contains($haystack, $key)) {
                return $domain;
            }
        }

        return null;
    }
}

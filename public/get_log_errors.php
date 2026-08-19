<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Unique errors in laravel-2026-08-18.log:\n";

$logFile = __DIR__ . '/../storage/logs/laravel-2026-08-18.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $seen = [];
    foreach ($lines as $line) {
        if (str_contains($line, '.ERROR:') || str_contains($line, 'exception') || str_contains($line, 'Exception')) {
            // Get first 150 chars of the error line to avoid spamming stacktraces
            $summary = substr($line, 0, 300);
            if (!in_array($summary, $seen)) {
                echo htmlspecialchars($summary) . "\n";
                $seen[] = $summary;
            }
        }
    }
} else {
    echo "Log file not found.\n";
}
echo "</pre>";

<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
echo "Starting Zoom Table Schema Diagnostic...\n";

try {
    $resZoom = Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE zoom_meetings");
    echo "\nzoom_meetings Table Schema:\n";
    print_r($resZoom[0]);
} catch (\Exception $e) {
    echo "\nzoom_meetings table does not exist or error: " . $e->getMessage() . "\n";
}
echo "</pre>";

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dir = 'd:/dev-codes/Official Codes/Zemwa/Zemwa Vendor/Custom Modules/pro-bundle';
$files = scandir($dir);

echo "<pre>";
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
        $zipPath = "$dir/$file";
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            echo "=== $file (Total files: " . $zip->numFiles . ") ===\n";
            for ($i = 0; $i < min(10, $zip->numFiles); $i++) {
                echo "  " . $zip->getNameIndex($i) . "\n";
            }
            if ($zip->numFiles > 10) {
                echo "  ...\n";
            }
            $zip->close();
        } else {
            echo "Failed to open $file\n";
        }
        echo "\n";
    }
}
echo "</pre>";

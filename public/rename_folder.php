<?php
header('Content-Type: text/plain');

$oldPath = 'd:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-web-core';
$newPath = 'd:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-mono-repo';

echo "Attempting to rename:\nOld: {$oldPath}\nNew: {$newPath}\n\n";

if (file_exists($newPath)) {
    echo "Error: Target directory already exists.\n";
    exit;
}

if (!file_exists($oldPath)) {
    echo "Error: Source directory does not exist.\n";
    exit;
}

try {
    if (rename($oldPath, $newPath)) {
        echo "Success! Directory renamed.\n";
    } else {
        echo "Rename failed (returned false).\n";
    }
} catch (\Exception $e) {
    echo "Exception caught during rename: " . $e->getMessage() . "\n";
}

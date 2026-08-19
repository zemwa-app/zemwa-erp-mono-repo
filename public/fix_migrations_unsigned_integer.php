<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Targeted Migration Regex Test...\n";

$targetFile = 'D:/dev-projects/dev-mithuntc/mbtech-projects/zemwa/zemwa-erp/zemwa-erp-web-core/Modules/LeadFormsPro/Database/Migrations/2024_01_02_071150_create_lfp_categories_table.php';

if (file_exists($targetFile)) {
    $content = file_get_contents($targetFile);
    echo "Original content containing company_id:\n";
    $lines = explode("\n", $content);
    foreach ($lines as $idx => $line) {
        if (str_contains($line, 'company_id')) {
            echo "  Line " . ($idx + 1) . ": [" . $line . "]\n";
        }
    }
    
    $pattern = '/\$table->(unsignedBigInteger|integer|bigInteger|unsignedInteger|int)\(\s*([\'"])company_id([\'"])\s*\)/i';
    
    $newContent = preg_replace($pattern, '$table->unsignedInteger($2company_id$3)', $content);
    
    if ($newContent !== $content) {
        echo "\nSUCCESS: Replaced content! Showing replaced lines:\n";
        $newLines = explode("\n", $newContent);
        foreach ($newLines as $idx => $line) {
            if (str_contains($line, 'company_id')) {
                echo "  Line " . ($idx + 1) . ": [" . $line . "]\n";
            }
        }
    } else {
        echo "\nFAILED: Regex did not modify content!\n";
    }
} else {
    echo "Target file not found at: $targetFile\n";
}
echo "</pre>";

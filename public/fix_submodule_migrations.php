<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Scanning custom submodule migrations...\n";

$modulesPath = realpath(__DIR__ . '/../Modules/ProBundle/Modules');
if (!$modulesPath) {
    die("Error: ProBundle Modules path not found.\n");
}

// 1. Rename the landing page categories migration to run before landing pages migration
$catOldFile = $modulesPath . '/LandingPagePro/Database/Migrations/2024_01_26_051602_create_landing_page_categories_table.php';
$catNewFile = $modulesPath . '/LandingPagePro/Database/Migrations/2024_01_22_145102_create_landing_page_categories_table.php';

if (file_exists($catOldFile)) {
    if (rename($catOldFile, $catNewFile)) {
        echo "Successfully renamed categories migration to run earlier.\n";
    } else {
        echo "Failed to rename categories migration.\n";
    }
} else {
    echo "Categories migration already renamed or not found at old path.\n";
}

// 2. Scan all migration files to fix company_id and other integer fields referencing companies/users bigIncrements
$dirIt = new RecursiveDirectoryIterator($modulesPath);
$it = new RecursiveIteratorIterator($dirIt);

$fixedCount = 0;
foreach ($it as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        
        $originalContent = $content;
        
        // Replace integer company_id with unsignedBigInteger
        $content = str_replace(
            [
                "\$table->unsignedInteger('company_id')",
                "\$table->integer('company_id')->unsigned()",
                "\$table->integer('company_id')->unsigned",
                "integer('company_id')"
            ],
            "\$table->unsignedBigInteger('company_id')",
            $content
        );

        // Also handle templates and other tables referencing company_id where they might have used integer
        // Check for references to id on companies
        // If there's an edit, save the file
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo "Fixed company_id datatype in: " . basename($filePath) . "\n";
            $fixedCount++;
        }
    }
}

echo "\nCompleted scanning and fixing $fixedCount migration files.\n";
echo "</pre>";

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PurchaseRequisitionImport;
use App\Http\Controllers\DashboardController;

class SyncSapData extends Command
{
    protected $signature = 'sap:sync';
    protected $description = 'Auto-import SAP files from storage/app/sap into the database';

    private const ALLOWED_EXT = ['xlsx', 'xls', 'csv', 'html', 'htm', 'mhtml', 'mht', 'xml', 'txt'];

    public function handle()
    {
        $sapDir = storage_path('app/sap');
        $processedDir = storage_path('app/sap/processed');

        File::ensureDirectoryExists($sapDir);
        File::ensureDirectoryExists($processedDir);

        $files = collect(File::files($sapDir))->filter(function ($file) {
            return in_array(strtolower($file->getExtension()), self::ALLOWED_EXT);
        });

        if ($files->isEmpty()) {
            $this->info('No new SAP files found in ' . $sapDir);
            return;
        }

        $this->info("Found {$files->count()} file(s) to process.");

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $filePath = $file->getRealPath();
            $extension = strtolower($file->getExtension());

            $this->info("Processing: {$filename}");

            try {
                $fileContent = file_get_contents($filePath);
                
                // Convert UTF-16 (common SAP encoding) to UTF-8
                if (substr($fileContent, 0, 2) === "\xFF\xFE") {
                    $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-16LE');
                } elseif (substr($fileContent, 0, 2) === "\xFE\xFF") {
                    $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-16BE');
                }
                // Remove UTF-8 BOM if present
                $fileContent = preg_replace('/^\xEF\xBB\xBF/', '', $fileContent);

                $header = substr($fileContent, 0, 2048);
                $imported = false;

                // 1) XLSX (ZIP format)
                if (substr($header, 0, 4) === "PK\x03\x04") {
                    Excel::import(new PurchaseRequisitionImport, $filePath, null, \Maatwebsite\Excel\Excel::XLSX);
                    $this->info('  → Imported as XLSX');
                    $imported = true;
                }

                // 2) True XLS (OLE format)
                if (!$imported && substr($header, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
                    Excel::import(new PurchaseRequisitionImport, $filePath, null, \Maatwebsite\Excel\Excel::XLS);
                    $this->info('  → Imported as true XLS');
                    $imported = true;
                }

                // 3) CSV
                if (!$imported && $extension === 'csv') {
                    Excel::import(new PurchaseRequisitionImport, $filePath, null, \Maatwebsite\Excel\Excel::CSV);
                    $this->info('  → Imported as CSV');
                    $imported = true;
                }

                // 4) Tab-separated text (SAP commonly exports .xls files as TSV)
                if (!$imported && strpos($fileContent, "\t") !== false && stripos($fileContent, '<table') === false) {
                    $this->info('  → Detected as tab-separated text (SAP TSV format)');
                    $count = $this->parseTsv($fileContent);
                    $this->info("  → Imported {$count} records");
                    $imported = true;
                }

                // 5) HTML/MHTML fallback
                if (!$imported) {
                    $this->info('  → Parsing as HTML/MHTML...');
                    $controller = app(DashboardController::class);
                    $ref = new \ReflectionMethod($controller, 'parseHtmlTable');
                    $ref->setAccessible(true);
                    $count = $ref->invoke($controller, $fileContent);
                    $this->info("  → Imported {$count} records (HTML parser)");
                }

                // Release memory before moving
                unset($fileContent, $header);
                gc_collect_cycles();
                
                // Small delay to ensure file handles are released
                usleep(500000);

                // Move to processed folder
                $timestamp = now()->format('Ymd_His');
                $newName = "{$timestamp}_{$filename}";
                $dest = $processedDir . DIRECTORY_SEPARATOR . $newName;
                
                if (!@rename($filePath, $dest)) {
                    // Fallback: copy then delete
                    copy($filePath, $dest);
                    @unlink($filePath);
                }
                $this->info("  → Moved to processed/{$newName}");

            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                \Log::error("SAP Sync error [{$filename}]: " . $e->getMessage());
            }
        }

        $this->info('SAP sync completed.');
    }

    /**
     * Parse tab-separated SAP export files.
     * Reuses normalizeHeaders, looksLikeHeader, saveRecord from DashboardController.
     */
    private function parseTsv(string $content): int
    {
        $controller = app(DashboardController::class);

        // Get references to the private methods
        $looksLikeHeader = new \ReflectionMethod($controller, 'looksLikeHeader');
        $looksLikeHeader->setAccessible(true);

        $normalizeHeaders = new \ReflectionMethod($controller, 'normalizeHeaders');
        $normalizeHeaders->setAccessible(true);

        $saveRecord = new \ReflectionMethod($controller, 'saveRecord');
        $saveRecord->setAccessible(true);

        $lines = explode("\n", $content);
        $headers = [];
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $cells = explode("\t", $line);
            $rowData = array_map('trim', $cells);

            // Skip rows with too few columns
            if (count(array_filter($rowData)) < 3) continue;

            // Detect header row
            if (empty($headers) && $looksLikeHeader->invoke($controller, $rowData)) {
                $headers = $normalizeHeaders->invoke($controller, $rowData);
                continue;
            }

            if (empty($headers)) continue;

            // Map row data to normalized headers
            $record = [];
            foreach ($headers as $idx => $key) {
                $record[$key] = $rowData[$idx] ?? null;
            }

            if (!empty($record['pr_number'])) {
                $saveRecord->invoke($controller, $record);
                $count++;
            }
        }

        return $count;
    }
}

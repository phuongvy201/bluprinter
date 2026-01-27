<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
    /**
     * Show import form
     */
    public function showImportForm()
    {
        $user = auth()->user();

        // Get templates for reference
        if ($user->hasRole('admin')) {
            $templates = ProductTemplate::with('category')->orderBy('name', 'asc')->get();
        } else {
            $templates = ProductTemplate::where('user_id', $user->id)
                ->with('category')
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('admin.products.import', compact('templates'));
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'template_id',
            'product_name',
            'price',
            'description',
            'quantity',
            'status',
            'image_1',
            'image_2',
            'image_3',
            'image_4',
            'image_5',
            'image_6',
            'image_7',
            'image_8',
            'video_url'
        ];

        $sampleData = [
            [
                '16',
                'Sample Product 1',
                '5.00',
                'Custom description for this product',
                '100',
                'active',
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg',
                '',
                '',
                '',
                '',
                '',
                '',
                'https://example.com/video.mp4'
            ],
            [
                '16',
                'Sample Product 2',
                '10.00',
                '',
                '50',
                'draft',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
        ];

        // Create CSV content
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $headers);
        foreach ($sampleData as $row) {
            fputcsv($csv, $row);
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="products_import_template.csv"');
    }

    /**
     * Process import file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // Generate unique progress key
            $progressKey = 'import_progress_' . uniqid();

            // Count total rows (excluding header) - memory efficient
            $file = $request->file('file');
            $totalRows = $this->countRows($file);

            // Ensure totalRows is at least 1
            if ($totalRows < 1) {
                $totalRows = 1; // Fallback to 1 if count fails
            }

            Log::info("Starting import", [
                'total_rows' => $totalRows,
                'progress_key' => $progressKey,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension()
            ]);

            // Create import instance with progress key
            $import = new ProductsImport(auth()->user(), $progressKey);
            $import->setTotalRows($totalRows);

            // If AJAX request, return progress key immediately
            if ($request->ajax() || $request->wantsJson()) {
                // Run import in background (for now, we'll do it synchronously but return immediately)
                // In production, you might want to use queues
                Excel::import($import, $file);

                $errors = $import->getErrors();
                $successCount = $import->getSuccessCount();

                // Mark as completed
                $import->markCompleted();

                return response()->json([
                    'success' => true,
                    'progress_key' => $progressKey,
                    'completed' => true,
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                    'errors' => array_slice($errors, 0, 10), // Return first 10 errors
                    'message' => "Successfully imported {$successCount} products!" . (count($errors) > 0 ? " (" . count($errors) . " errors)" : ''),
                ]);
            }

            // Regular form submission (non-AJAX)
            Excel::import($import, $file);

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            // Mark as completed
            $import->markCompleted();

            if (count($errors) > 0) {
                $errorMessage = "Imported {$successCount} products successfully. Errors: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= " (and " . (count($errors) - 5) . " more errors)";
                }

                return redirect()->route('admin.products.index')
                    ->with('error', $errorMessage);
            }

            return redirect()->route('admin.products.index')
                ->with('success', "Successfully imported {$successCount} products!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Import failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get import progress
     */
    public function getProgress(Request $request)
    {
        $progressKey = $request->input('progress_key');

        if (!$progressKey) {
            return response()->json([
                'error' => 'Progress key is required',
            ], 400);
        }

        $progress = Cache::get($progressKey, [
            'processed' => 0,
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'percentage' => 0,
            'status' => 'unknown',
        ]);

        return response()->json($progress);
    }

    /**
     * Count total rows in file (excluding header) - Memory efficient
     * Uses streaming to avoid loading entire file into memory
     */
    protected function countRows($file): int
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $tempPath = $file->getRealPath();

            // If file is uploaded (not real path), store temporarily
            if (!$tempPath || !file_exists($tempPath)) {
                $tempPath = $file->store('temp');
                $tempPath = storage_path('app/' . $tempPath);
                $isTempFile = true;
            } else {
                $isTempFile = false;
            }

            $count = 0;

            // For CSV files, count line by line (memory efficient)
            if ($extension === 'csv') {
                $handle = fopen($tempPath, 'r');
                if ($handle) {
                    // Skip header row
                    fgetcsv($handle);
                    // Count remaining rows
                    while (($line = fgetcsv($handle)) !== false) {
                        // Only count non-empty rows
                        if (!empty(array_filter($line))) {
                            $count++;
                        }
                    }
                    fclose($handle);
                }
            } else {
                // For Excel files, use streaming reader (more memory efficient)
                // Use a simple row counter that doesn't load data
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
                    $extension === 'xlsx' ? 'Xlsx' : 'Xls'
                );
                $reader->setReadDataOnly(true); // Only read data, not formatting
                $reader->setReadEmptyCells(false);

                $spreadsheet = $reader->load($tempPath);
                $worksheet = $spreadsheet->getActiveSheet();

                // Count non-empty rows (skip header)
                $highestRow = $worksheet->getHighestRow();
                $count = $highestRow - 1; // Subtract header

                // Clean up spreadsheet from memory
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $worksheet, $reader);
            }

            // Clean up temp file if we created one
            if ($isTempFile && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return max(0, $count);
        } catch (\Exception $e) {
            Log::warning("Failed to count rows efficiently: " . $e->getMessage() . " - Using fallback estimation");

            // Fallback: estimate based on file size (rough estimate)
            // This is less accurate but doesn't use memory
            $fileSize = $file->getSize();
            // Rough estimate: ~300 bytes per row for CSV, ~500 for Excel
            $bytesPerRow = (strtolower($file->getClientOriginalExtension()) === 'csv') ? 300 : 500;
            $estimated = max(1, intval($fileSize / $bytesPerRow));
            Log::info("Estimated rows based on file size: {$estimated} rows");
            return $estimated;
        }
    }
}

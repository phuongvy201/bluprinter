<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

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
            $import = new ProductsImport(auth()->user());
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

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
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}

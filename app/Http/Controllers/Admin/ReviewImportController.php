<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ReviewsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReviewImportController extends Controller
{
    public function showImportForm()
    {
        return view('admin.reviews.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'product_id',
            'product_slug',
            'customer_name',
            'customer_email',
            'rating',
            'review_text',
            'is_verified_purchase',
            'is_approved',
            'review_date',
            'image_1',
            'image_2',
            'image_3',
        ];

        $sampleData = [
            [
                '101',
                '',
                'Jane Doe',
                'jane@example.com',
                '5',
                'Great quality and fast shipping!',
                '1',
                '1',
                '2026-05-12',
                'https://example.com/review-photo-1.jpg',
                'https://example.com/review-photo-2.jpg',
                '',
            ],
            [
                '',
                'sample-product-slug',
                'John Smith',
                '',
                '4',
                'Love the design.',
                '0',
                '1',
                '',
                '',
                '',
                '',
            ],
        ];

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
            ->header('Content-Disposition', 'attachment; filename="reviews_import_template.csv"');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $user = $request->user();
        $import = new ReviewsImport($user);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.reviews.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        $successCount = $import->getSuccessCount();
        $errors = $import->getErrors();

        if ($successCount === 0 && empty($errors)) {
            return redirect()
                ->route('admin.reviews.import')
                ->with('warning', 'No rows were imported. Check that your file has data rows below the header.');
        }

        $message = "Imported {$successCount} review(s) successfully.";
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
            $message .= ' Some rows had errors.';
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', $message);
    }
}

<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ProductsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    protected $errors = [];
    protected $successCount = 0;
    protected $user;

    /**
     * Constructor
     */
    public function __construct($user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Check if template exists
            $template = ProductTemplate::find($row['template_id']);
            if (!$template) {
                $this->errors[] = "Row {$row['product_name']}: Template ID {$row['template_id']} not found";
                return null;
            }

            // Check ownership: Only admin can use any template, seller can only use their own
            if (!$this->user->hasRole('admin') && $template->user_id !== $this->user->id) {
                $this->errors[] = "Row {$row['product_name']}: You don't have permission to use Template ID {$row['template_id']}";
                return null;
            }

            // Calculate final price: base_price + additional price (if provided)
            $finalPrice = $template->base_price;
            if (!empty($row['price'])) {
                $finalPrice = $template->base_price + floatval($row['price']);
            }

            // Use custom description if provided, otherwise use template description
            $description = !empty($row['description']) ? $row['description'] : $template->description;

            // Collect and upload media to S3 (up to 8 images + 1 video)
            $mediaUrls = [];

            // Process images (image_1 to image_8)
            for ($i = 1; $i <= 8; $i++) {
                $imageKey = 'image_' . $i;
                if (!empty($row[$imageKey])) {
                    $s3Url = $this->downloadAndUploadToS3($row[$imageKey], 'products/images');
                    if ($s3Url) {
                        $mediaUrls[] = $s3Url;
                    } else {
                        $this->errors[] = "Row {$row['product_name']}: Failed to upload image_{$i} to S3";
                    }
                }
            }

            // Process video
            if (!empty($row['video_url'])) {
                $s3Url = $this->downloadAndUploadToS3($row['video_url'], 'products/videos');
                if ($s3Url) {
                    $mediaUrls[] = $s3Url;
                } else {
                    $this->errors[] = "Row {$row['product_name']}: Failed to upload video to S3";
                }
            }

            // If no media uploaded successfully, use template media
            if (empty($mediaUrls) && !empty($template->media)) {
                $mediaUrls = $template->media;
            }

            // Create product
            $product = Product::create([
                'template_id' => $row['template_id'],
                'user_id' => $this->user->id, // Product owner
                'shop_id' => $this->user->hasShop() ? $this->user->shop->id : null, // Shop ID
                'name' => $row['product_name'],
                'slug' => Str::slug($row['product_name']),
                'price' => $finalPrice,
                'description' => $description,
                'quantity' => $row['quantity'] ?? 0,
                'status' => $row['status'] ?? 'active',
                'media' => $mediaUrls ?: null, // Laravel auto-cast to JSON via $casts
            ]);

            // Create variants if template has variants
            if ($template->variants && $template->variants->count() > 0) {
                foreach ($template->variants as $templateVariant) {
                    // Parse variant name to get size and color
                    $variantName = $templateVariant->variant_name;
                    $parts = explode('/', $variantName);

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'template_id' => $template->id,
                        'variant_name' => $variantName,
                        'size' => $parts[0] ?? null,
                        'color' => $parts[1] ?? null,
                        'price' => $templateVariant->price ?? null,
                        'sku' => 'SKU-' . strtoupper(Str::random(8)),
                        'quantity' => $templateVariant->quantity ?? 0,
                        'media' => null,
                    ]);
                }
            }

            // Increment shop products count
            if ($this->user->hasShop()) {
                $this->user->shop->incrementProducts();
            }

            $this->successCount++;
            return $product;
        } catch (\Exception $e) {
            $this->errors[] = "Row {$row['product_name']}: {$e->getMessage()}";
            Log::error("Import error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'template_id' => 'required|exists:product_templates,id',
            'product_name' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,draft',
            'image_1' => 'nullable|url',
            'image_2' => 'nullable|url',
            'image_3' => 'nullable|url',
            'image_4' => 'nullable|url',
            'image_5' => 'nullable|url',
            'image_6' => 'nullable|url',
            'image_7' => 'nullable|url',
            'image_8' => 'nullable|url',
            'video_url' => 'nullable|url',
        ];
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    /**
     * Handle validation failures
     */
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
        }
    }

    /**
     * Batch insert size
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Download file from URL and upload to S3
     * 
     * @param string $url
     * @param string $folder
     * @return string|null S3 URL or null on failure
     */
    protected function downloadAndUploadToS3(string $url, string $folder = 'products'): ?string
    {
        try {
            // Download file from URL
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to download file from URL: {$url}");
                return null;
            }

            // Get file content
            $fileContent = $response->body();

            // Get file extension from URL or content type
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $contentType = $response->header('Content-Type');
                $extension = $this->getExtensionFromContentType($contentType);
            }

            // Generate unique filename
            $filename = $folder . '/' . Str::random(40) . '.' . $extension;

            // Upload to S3
            $disk = Storage::disk('s3');
            $uploaded = $disk->put($filename, $fileContent, 'public');

            if ($uploaded) {
                return $disk->url($filename);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error uploading to S3: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get file extension from content type
     * 
     * @param string|null $contentType
     * @return string
     */
    protected function getExtensionFromContentType(?string $contentType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
        ];

        return $map[$contentType] ?? 'jpg';
    }
}

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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\QueryException;
use Illuminate\Http\File;

class ProductsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    SkipsEmptyRows,
    WithBatchInserts,
    WithChunkReading,
    WithEvents
{
    protected $errors = [];
    protected $successCount = 0;
    protected $user;
    protected $importedProducts = []; // Store products that need variants created
    protected $variantsCreated = false; // Flag to ensure variants are only created once

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
            // Skip empty rows - check if required fields are empty
            $templateId = isset($row['template_id']) ? trim((string)$row['template_id']) : '';
            $productName = isset($row['product_name']) ? trim((string)$row['product_name']) : '';

            // If both template_id and product_name are empty, skip this row (empty row)
            if (empty($templateId) && empty($productName)) {
                return null;
            }

            // Validate required fields - if one is empty but the other is not, it's an error
            if (empty($templateId)) {
                $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": template_id is required";
                return null;
            }

            if (empty($productName)) {
                $this->errors[] = "Row with template_id {$templateId}: product_name is required";
                return null;
            }

            // Check if template exists and load with variants
            $template = ProductTemplate::with('variants')->find($templateId);
            if (!$template) {
                $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": Template ID {$templateId} not found";
                return null;
            }

            // Check ownership: Only admin can use any template, seller can only use their own
            if (!$this->user->hasRole('admin') && $template->user_id !== $this->user->id) {
                $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": You don't have permission to use Template ID {$templateId}";
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
                    $imageUrl = trim($row[$imageKey]);
                    // Validate URL format
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": Invalid URL for image_{$i}: {$imageUrl}";
                        continue;
                    }

                    $s3Url = $this->downloadAndUploadToS3($imageUrl);
                    if ($s3Url) {
                        $mediaUrls[] = $s3Url;
                    } else {
                        // Log warning but don't fail the entire import - will use template media as fallback
                        Log::warning("Failed to upload image_{$i} to S3 for product: {$productName}, URL: {$imageUrl}");
                        $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": Failed to upload image_{$i} to S3 (URL: " . Str::limit($imageUrl, 50) . ")";
                    }
                }
            }

            // Process video
            if (!empty($row['video_url'])) {
                $videoUrl = trim($row['video_url']);
                // Validate URL format
                if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                    $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": Invalid URL for video: {$videoUrl}";
                } else {
                    $s3Url = $this->downloadAndUploadToS3($videoUrl);
                    if ($s3Url) {
                        $mediaUrls[] = $s3Url;
                    } else {
                        // Log warning but don't fail the entire import
                        Log::warning("Failed to upload video to S3 for product: {$productName}, URL: {$videoUrl}");
                        $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": Failed to upload video to S3 (URL: " . Str::limit($videoUrl, 50) . ")";
                    }
                }
            }

            // If no media uploaded successfully, use template media
            if (empty($mediaUrls) && !empty($template->media)) {
                $mediaUrls = $template->media;
            }

            // Check if product with same name and template already exists (prevent duplicates)
            $existingProduct = Product::where('name', $productName)
                ->where('template_id', $templateId)
                ->where('user_id', $this->user->id)
                ->first();

            if ($existingProduct) {
                $this->errors[] = "Row {$productName}: Product with this name and template already exists (ID: {$existingProduct->id})";
                Log::warning("Skipping duplicate product: {$productName}, Template ID: {$templateId}, Existing ID: {$existingProduct->id}");
                return null;
            }

            // Generate unique slug to avoid duplicates
            $baseSlug = Str::slug($productName);
            $slug = $baseSlug;
            $counter = 1;

            // Check if slug already exists and make it unique
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Create product with error handling for duplicates
            // IMPORTANT: Never set 'id' - let database auto-increment
            try {
                // Prepare data array - explicitly exclude 'id' to ensure auto-increment
                $productData = [
                    'template_id' => $templateId,
                    'user_id' => $this->user->id, // Product owner
                    'shop_id' => $this->user->hasShop() ? $this->user->shop->id : null, // Shop ID
                    'name' => $productName,
                    'slug' => $slug,
                    'price' => $finalPrice,
                    'description' => $description,
                    'quantity' => $row['quantity'] ?? 0,
                    'status' => $row['status'] ?? 'active',
                    'media' => $mediaUrls ?: null, // Laravel auto-cast to JSON via $casts
                ];

                // Ensure 'id' is not in the data (even if somehow included)
                unset($productData['id']);

                // Create product - variants will be created after import completes
                $product = new Product($productData);

                // Store template info for creating variants after import (batch insert doesn't trigger created event)
                if ($template->variants && $template->variants->count() > 0) {
                    $this->importedProducts[] = [
                        'slug' => $slug,
                        'template_id' => $templateId,
                        'template_variants' => $template->variants,
                    ];
                }

                $this->successCount++;
                return $product;
            } catch (QueryException $e) {
                // Handle duplicate entry or other database errors
                if ($e->getCode() == 23000) { // Integrity constraint violation
                    $errorMessage = $e->getMessage();
                    if (strpos($errorMessage, 'Duplicate entry') !== false) {
                        $this->errors[] = "Row {$productName}: Product already exists in database (duplicate entry). Please check if you're importing the same file twice.";
                        Log::error("Duplicate product entry: {$productName}, Error: " . $errorMessage);
                    } else {
                        $this->errors[] = "Row {$productName}: Database error - " . $errorMessage;
                        Log::error("Database error for product: {$productName}, Error: " . $errorMessage);
                    }
                    return null;
                }
                throw $e; // Re-throw if it's a different error
            }
        } catch (\Exception $e) {
            $productName = isset($row['product_name']) ? trim((string)$row['product_name']) : 'Unknown';
            $this->errors[] = "Row " . ($productName ?: 'Unknown') . ": {$e->getMessage()}";
            Log::error("Import error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validation rules
     * Note: template_id and product_name are nullable here because we check for empty rows in model() method
     * If both are empty, the row will be skipped. If only one is empty, validation will catch it.
     */
    public function rules(): array
    {
        return [
            'template_id' => 'nullable|exists:product_templates,id',
            'product_name' => 'nullable|string|max:255',
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
     * Note: Using smaller batch size to avoid ID conflicts
     */
    public function batchSize(): int
    {
        return 50; // Reduced from 100 to avoid potential ID conflicts
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 50; // Reduced from 100 to match batch size
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
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // After all products are imported, create variants for them
                // This is needed because batch insert doesn't trigger created event
                // Only create variants once (AfterSheet can be called multiple times for multiple sheets)
                if (!$this->variantsCreated) {
                    $this->createVariantsForImportedProducts();
                    $this->variantsCreated = true;
                }
            },
        ];
    }

    /**
     * Create variants for imported products
     */
    protected function createVariantsForImportedProducts(): void
    {
        if (empty($this->importedProducts)) {
            Log::info("No products need variants created");
            return;
        }

        Log::info("Starting to create variants for imported products", [
            'products_count' => count($this->importedProducts)
        ]);

        foreach ($this->importedProducts as $productInfo) {
            try {
                $product = Product::where('slug', $productInfo['slug'])->first();
                if (!$product) {
                    Log::warning("Product not found for slug: {$productInfo['slug']}, cannot create variants");
                    continue;
                }

                // Check if product already has variants
                if ($product->variants()->count() > 0) {
                    Log::info("Product already has variants, skipping", [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'existing_variants_count' => $product->variants()->count()
                    ]);
                    continue;
                }

                $templateVariants = $productInfo['template_variants'];
                Log::info("Creating variants for product", [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'template_id' => $productInfo['template_id'],
                    'template_variants_count' => $templateVariants->count()
                ]);

                foreach ($templateVariants as $templateVariant) {
                    $variantName = $templateVariant->variant_name;

                    // Get attributes from template variant (preferred)
                    $attributes = $templateVariant->attributes ?? [];

                    // If no attributes from template, try to parse from variant_name
                    if (empty($attributes)) {
                        $attributes = $this->parseAttributesFromVariantName($variantName);
                    }

                    // If still no attributes, create a generic one
                    if (empty($attributes)) {
                        $attributes = ['Variant' => $variantName];
                    }

                    try {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'template_id' => $productInfo['template_id'],
                            'variant_name' => $variantName,
                            'attributes' => $attributes,
                            'price' => $templateVariant->price ?? null,
                            'sku' => 'SKU-' . strtoupper(Str::random(8)),
                            'quantity' => $templateVariant->quantity ?? 0,
                            'media' => null,
                        ]);

                        Log::info("Variant created successfully", [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'variant_name' => $variantName,
                            'attributes' => $attributes
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create variant", [
                            'product_id' => $product->id,
                            'variant_name' => $variantName,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $this->errors[] = "Failed to create variant '{$variantName}' for product '{$product->name}': " . $e->getMessage();
                    }
                }

                Log::info("Completed creating variants for product", [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variants_created' => $product->variants()->count()
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to create variants for product with slug: {$productInfo['slug']}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->errors[] = "Failed to create variants for product with slug: {$productInfo['slug']}, Error: " . $e->getMessage();
            }
        }

        Log::info("Finished creating variants for imported products", [
            'total_products_processed' => count($this->importedProducts)
        ]);
    }

    /**
     * Parse attributes from variant name (fallback method)
     * 
     * @param string $variantName
     * @return array
     */
    protected function parseAttributesFromVariantName(string $variantName): array
    {
        $attributes = [];

        // Common size patterns
        $sizePatterns = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Small', 'Medium', 'Large', '11oz', '12oz', '15oz'];
        $colorPatterns = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Pink', 'Gray', 'Grey', 'Brown', 'Orange', 'Navy', 'Maroon', 'Teal'];

        // Handle format like "Black/S" or "Black S" or "Black-S" or "S/Black"
        $variantName = str_replace(['/', '-'], ' ', $variantName);
        $nameParts = array_filter(explode(' ', trim($variantName)));

        foreach ($nameParts as $part) {
            $part = trim($part);
            if (in_array($part, $sizePatterns)) {
                $attributes['Size'] = $part;
            } elseif (in_array($part, $colorPatterns)) {
                $attributes['Color'] = $part;
            } else {
                // If it's not a common size/color, treat as additional attribute
                if (!isset($attributes['Material'])) {
                    $attributes['Material'] = $part;
                } elseif (!isset($attributes['Style'])) {
                    $attributes['Style'] = $part;
                }
            }
        }

        return $attributes;
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
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                Log::warning("Invalid URL format: {$url}");
                return null;
            }

            // Download file from URL with timeout, retry, and proper headers
            // Some sites like Etsy block requests without User-Agent
            // Use manual retry with exponential backoff for connection reset errors
            $maxRetries = 3;
            $response = null;
            $lastError = null;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = Http::timeout(60) // Increased timeout to 60 seconds
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                            'Accept-Language' => 'en-US,en;q=0.9',
                            'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST),
                            'Connection' => 'keep-alive',
                            'Accept-Encoding' => 'gzip, deflate, br',
                        ])
                        ->withOptions([
                            'allow_redirects' => true,
                            'max_redirects' => 5,
                            'verify' => false, // Disable SSL verification if needed (for some CDNs)
                            'curl' => [
                                CURLOPT_TCP_KEEPALIVE => 1,
                                CURLOPT_TCP_KEEPIDLE => 30,
                                CURLOPT_TCP_KEEPINTVL => 10,
                            ],
                        ])
                        ->get($url);

                    if ($response->successful()) {
                        break; // Success, exit retry loop
                    } else {
                        $lastError = "HTTP {$response->status()}";
                        if ($attempt < $maxRetries) {
                            $delay = pow(2, $attempt) * 1000; // Exponential backoff: 2s, 4s, 8s
                            Log::warning("Download attempt {$attempt} failed for: {$url}, Status: {$response->status()}, Retrying in {$delay}ms");
                            usleep($delay * 1000); // Convert to microseconds
                        }
                    }
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastError = $e->getMessage();
                    if ($attempt < $maxRetries) {
                        $delay = pow(2, $attempt) * 1000; // Exponential backoff: 2s, 4s, 8s
                        Log::warning("Connection error on attempt {$attempt} for: {$url}, Error: {$lastError}, Retrying in {$delay}ms");
                        usleep($delay * 1000); // Convert to microseconds
                    } else {
                        Log::error("Connection error after {$maxRetries} attempts: {$url}, Error: {$lastError}");
                    }
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::error("Unexpected error downloading: {$url}, Error: {$lastError}");
                    return null; // Don't retry for unexpected errors
                }
            }

            if (!$response || !$response->successful()) {
                Log::warning("Failed to download file from URL after {$maxRetries} attempts: {$url}, Last error: {$lastError}");
                return null;
            }

            // Check if response has content
            $fileContent = $response->body();
            if (empty($fileContent)) {
                Log::warning("Empty file content from URL: {$url}");
                return null;
            }

            // Check content type to ensure it's an image or video
            $contentType = $response->header('Content-Type');
            $isValidMedia = false;
            if ($contentType) {
                $validTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'video/mp4',
                    'video/mpeg',
                    'video/quicktime',
                    'video/x-msvideo'
                ];
                $isValidMedia = in_array(strtolower(explode(';', $contentType)[0]), $validTypes);
            }

            // If content type is not valid but we have content, still try to process (some servers don't send proper headers)
            if (!$isValidMedia && $contentType && !str_starts_with($contentType, 'image/') && !str_starts_with($contentType, 'video/')) {
                Log::warning("Invalid content type from URL: {$url}, Content-Type: {$contentType}");
                // Don't return null here - some servers don't send proper headers but still serve valid images
            }

            // Check file size (max 10MB for images, 50MB for videos)
            $fileSize = strlen($fileContent);
            $isVideo = $contentType && str_starts_with($contentType, 'video/');
            $maxSize = $isVideo ? (50 * 1024 * 1024) : (10 * 1024 * 1024); // 50MB for videos, 10MB for images
            if ($fileSize > $maxSize) {
                Log::warning("File too large from URL: {$url}, Size: " . round($fileSize / 1024 / 1024, 2) . "MB, Max: " . round($maxSize / 1024 / 1024, 2) . "MB");
                return null;
            }

            // Minimum file size check (very small files are likely errors)
            if ($fileSize < 100) {
                Log::warning("File too small from URL: {$url}, Size: {$fileSize} bytes");
                return null;
            }

            // Get file extension from URL or content type
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension) && $contentType) {
                $extension = $this->getExtensionFromContentType($contentType);
            }

            // If still no extension, try to detect from file content (magic bytes)
            if (empty($extension)) {
                $extension = $this->detectExtensionFromContent($fileContent);
            }

            // Final fallback
            if (empty($extension)) {
                $extension = 'jpg'; // Default extension
            }

            // Clean extension (remove query parameters if any)
            $extension = explode('?', $extension)[0];
            $extension = strtolower($extension);

            // Generate unique filename - same format as ProductController
            $fileName = time() . '_' . Str::random(10) . '.' . $extension;

            // Upload to S3 - use same method as ProductController
            $disk = Storage::disk('s3');
            if (!$disk) {
                Log::error("S3 disk not configured");
                return null;
            }

            // Validate S3 configuration
            $s3Config = config('filesystems.disks.s3');
            if (empty($s3Config['key']) || empty($s3Config['secret']) || empty($s3Config['bucket'])) {
                Log::error("S3 credentials not configured properly", [
                    'has_key' => !empty($s3Config['key']),
                    'has_secret' => !empty($s3Config['secret']),
                    'has_bucket' => !empty($s3Config['bucket']),
                    'bucket' => $s3Config['bucket'] ?? 'not set'
                ]);
                return null;
            }

            // Create temporary file from content to use putFileAs like ProductController
            $tempFile = tempnam(sys_get_temp_dir(), 'import_media_');
            if ($tempFile === false) {
                Log::error("Failed to create temporary file for: {$url}");
                return null;
            }

            try {
                // Write content to temporary file
                $bytesWritten = file_put_contents($tempFile, $fileContent);
                if ($bytesWritten === false || $bytesWritten === 0) {
                    Log::error("Failed to write content to temporary file for: {$url}");
                    return null;
                }

                // Create File object from temporary file (like ProductController uses UploadedFile)
                $fileObject = new File($tempFile);

                // Use putFileAs like ProductController does
                try {
                    // Check if file exists and is readable
                    if (!file_exists($tempFile) || !is_readable($tempFile)) {
                        Log::error("Temporary file not accessible: {$tempFile}, URL: {$url}");
                        return null;
                    }

                    $filePath = $disk->putFileAs('products', $fileObject, $fileName);

                    if ($filePath && !empty($filePath)) {
                        // Create the correct S3 URL format - same as ProductController
                        $s3Url = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $filePath;
                        Log::info("Successfully uploaded to S3", [
                            'file' => $fileName,
                            'path' => $filePath,
                            'url' => $s3Url,
                            'size' => $fileSize,
                            'source_url' => $url
                        ]);
                        return $s3Url;
                    } else {
                        // putFileAs returned false or empty - check S3 connection
                        Log::error("putFileAs returned false/empty for: {$fileName}", [
                            'url' => $url,
                            'temp_file' => $tempFile,
                            'temp_file_exists' => file_exists($tempFile),
                            'temp_file_size' => file_exists($tempFile) ? filesize($tempFile) : 0,
                            's3_bucket' => config('filesystems.disks.s3.bucket'),
                            's3_region' => config('filesystems.disks.s3.region'),
                            'has_s3_key' => !empty(config('filesystems.disks.s3.key'))
                        ]);
                        return null;
                    }
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    Log::error("AWS S3 Exception during upload: {$url}", [
                        'file' => $fileName,
                        'error_code' => $e->getAwsErrorCode(),
                        'error_message' => $e->getAwsErrorMessage(),
                        'request_id' => $e->getAwsRequestId(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                } catch (\Exception $e) {
                    Log::error("General exception during putFileAs: {$url}", [
                        'file' => $fileName,
                        'error' => $e->getMessage(),
                        'error_class' => get_class($e),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return null;
                }
            } catch (\Exception $e) {
                Log::error("Exception during S3 upload: {$url}, Error: " . $e->getMessage(), [
                    'file' => $fileName,
                    'trace' => $e->getTraceAsString()
                ]);
                return null;
            } finally {
                // Always clean up temporary file
                if (isset($tempFile) && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        } catch (\Exception $e) {
            // Catch any unexpected errors that weren't handled above
            Log::error("Unexpected error in downloadAndUploadToS3: {$url}, Error: " . $e->getMessage(), [
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
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

    /**
     * Detect file extension from file content (magic bytes)
     * 
     * @param string $content
     * @return string
     */
    protected function detectExtensionFromContent(string $content): string
    {
        if (empty($content)) {
            return 'jpg';
        }

        // Get first few bytes (magic bytes)
        $header = substr($content, 0, 12);

        // Check for image types
        if (substr($header, 0, 2) === "\xFF\xD8") {
            return 'jpg'; // JPEG
        }
        if (substr($header, 0, 8) === "\x89PNG\r\n\x1A\n") {
            return 'png'; // PNG
        }
        if (substr($header, 0, 6) === "GIF87a" || substr($header, 0, 6) === "GIF89a") {
            return 'gif'; // GIF
        }
        if (substr($header, 0, 4) === "RIFF" && substr($header, 8, 4) === "WEBP") {
            return 'webp'; // WebP
        }

        // Check for video types
        if (substr($header, 4, 4) === "ftyp") {
            // MP4 or MOV
            if (strpos($header, "mp4") !== false || strpos($header, "isom") !== false) {
                return 'mp4';
            }
            if (strpos($header, "qt") !== false) {
                return 'mov';
            }
        }

        // Default to jpg if cannot detect
        return 'jpg';
    }
}

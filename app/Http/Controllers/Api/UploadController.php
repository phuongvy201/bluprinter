<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class UploadController extends Controller
{
    /**
     * Generate presigned URLs for direct upload to S3
     */
    public function generatePresignedUrls(Request $request)
    {
        // Validate API token
        $token = $this->validateApiToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token'
            ], 401)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        // Check permissions
        if (!$token->hasPermission('product:create')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        // Validate request - support both old and new field names
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|min:1|max:10',
            'files.*.filename' => 'required_without:files.*.name|string|max:255',
            'files.*.name' => 'required_without:files.*.filename|string|max:255',
            'files.*.content_type' => 'required_without:files.*.type|string|in:image/jpeg,image/jpg,image/png,image/webp,video/mp4,video/avi,video/mov,video/webm',
            'files.*.type' => 'required_without:files.*.content_type|string|in:image/jpeg,image/jpg,image/png,image/webp,video/mp4,video/avi,video/mov,video/webm',
            'files.*.file_size' => 'nullable|integer|max:104857600', // 100MB max
            'files.*.size' => 'nullable|integer|max:104857600', // 100MB max
            'product_id' => 'nullable|exists:products,id', // For existing products
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        try {
            // Initialize S3 client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region', 'us-east-1'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $bucket = config('filesystems.disks.s3.bucket');
            $presignedUrls = [];
            $expiresIn = 15 * 60; // 15 minutes

            // Debug: Log current time
            \Log::info('Current server time', [
                'now' => now()->toISOString(),
                'timestamp' => time(),
                'expires_in' => $expiresIn
            ]);

            foreach ($request->input('files') as $index => $file) {
                // Support both old and new field names
                $originalName = $file['filename'] ?? $file['name'];
                $contentType = $file['content_type'] ?? $file['type'];
                $fileSize = $file['file_size'] ?? $file['size'] ?? 0;

                // Generate unique filename
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $filename = time() . '_' . uniqid() . '_' . $index . '.' . $extension;

                // Determine folder based on file type
                $folder = strpos($contentType, 'video/') === 0 ? 'products/videos' : 'products/images';
                $key = $folder . '/' . $filename;

                // Generate presigned URL using correct AWS SDK v3 syntax
                $cmd = $s3Client->getCommand('PutObject', [
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'ContentType' => $contentType,
                ]);

                $presignedRequest = $s3Client->createPresignedRequest($cmd, '+15 minutes');
                $presignedUrl = (string) $presignedRequest->getUri();

                // Debug: Log presigned URL details
                \Log::info('Generated presigned URL', [
                    'key' => $key,
                    'expires_in' => $expiresIn,
                    'url' => $presignedUrl,
                    'current_time' => now()->toISOString(),
                    'expires_at' => now()->addSeconds($expiresIn)->toISOString()
                ]);

                // Generate public URL for after upload
                $publicUrl = "https://s3.us-east-1.amazonaws.com/{$bucket}/{$key}";

                $presignedUrls[] = [
                    'index' => $index,
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'type' => $contentType,
                    'size' => $fileSize,
                    'upload_url' => (string) $presignedUrl,
                    'final_url' => $publicUrl,
                    'key' => $key,
                    'expires_in' => $expiresIn,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Presigned URLs generated successfully',
                'data' => [
                    'presigned_urls' => $presignedUrls,
                    'expires_in' => $expiresIn,
                    'bucket' => $bucket,
                ]
            ], 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        } catch (AwsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate presigned URLs: ' . $e->getMessage()
            ], 500)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate presigned URLs: ' . $e->getMessage()
            ], 500)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }
    }

    /**
     * Confirm upload completion and update product
     */
    public function confirmUpload(Request $request)
    {
        // Validate API token
        $token = $this->validateApiToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token'
            ], 401);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'uploaded_files' => 'required|array',
            'uploaded_files.*.key' => 'required|string',
            'uploaded_files.*.public_url' => 'required|url',
            'uploaded_files.*.type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = \App\Models\Product::findOrFail($request->product_id);

            // Get current media
            $currentMedia = $product->media ?? [];

            // Add new uploaded files to media
            foreach ($request->uploaded_files as $file) {
                $currentMedia[] = $file['public_url'];
            }

            // Update product with new media
            $product->update([
                'media' => $currentMedia
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload confirmed and product updated',
                'data' => [
                    'product_id' => $product->id,
                    'media_count' => count($currentMedia),
                    'media' => $currentMedia
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate API token
     */
    private function validateApiToken(Request $request)
    {
        $tokenValue = $request->header('X-API-Token') ?? $request->input('api_token');

        if (!$tokenValue) {
            return null;
        }

        $token = ApiToken::where('token', $tokenValue)->first();

        if (!$token || !$token->isValid()) {
            return null;
        }

        return $token;
    }
}

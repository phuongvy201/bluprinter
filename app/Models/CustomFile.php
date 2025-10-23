<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CustomFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'original_name',
        'filename',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'file_extension',
        'metadata',
        'status',
        'error_message',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeForUser($query, $userId = null, $sessionId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        if ($sessionId) {
            return $query->where('session_id', $sessionId);
        }

        return $query;
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'processed')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Accessors
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsDocumentAttribute()
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ]);
    }

    // Methods
    public function markAsProcessed()
    {
        $this->update(['status' => 'processed']);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function setExpiration($hours = 24)
    {
        $this->update([
            'expires_at' => now()->addHours($hours)
        ]);
    }

    public function extendExpiration($hours = 24)
    {
        $newExpiration = $this->expires_at
            ? $this->expires_at->addHours($hours)
            : now()->addHours($hours);

        $this->update(['expires_at' => $newExpiration]);
    }

    public function cleanup()
    {
        // This method can be called to clean up expired files
        if ($this->is_expired) {
            // Delete from S3 (implement S3 deletion logic)
            $this->delete();
        }
    }

    // Static methods
    public static function cleanupExpiredFiles()
    {
        return static::expired()->delete();
    }

    public static function generateFilename($originalName, $productId, $userId = null, $sessionId = null)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = now()->format('YmdHis');
        $random = str_random(8);

        $prefix = $userId ? "user_{$userId}" : "session_{$sessionId}";

        return "custom_files/{$prefix}/product_{$productId}/{$timestamp}_{$random}.{$extension}";
    }

    public static function getStoragePath($filename)
    {
        return "custom_files/{$filename}";
    }

    public static function getPublicUrl($filePath)
    {
        return config('filesystems.disks.s3.url') . '/' . $filePath;
    }
}

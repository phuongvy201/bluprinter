<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'description',
        'permissions',
        'default_shop_id',
        'is_active',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a new API token
     */
    public static function generateToken($name, $description = null, $permissions = ['product:create'])
    {
        return self::create([
            'name' => $name,
            'token' => 'bluprinter_' . Str::random(60),
            'description' => $description,
            'permissions' => $permissions,
            'is_active' => true,
        ]);
    }

    /**
     * Check if token is valid
     */
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if token has specific permission
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get the default shop for this API token
     */
    public function defaultShop()
    {
        return $this->belongsTo(\App\Models\Shop::class, 'default_shop_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'store_name',
        'product_categories',
        'message',
        'status',
        'approved_at',
        'rejected_at',
        'reviewed_by',
    ];
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRecommendation extends Model
{
    public const SOURCE_CO_OCCURRENCE = 'co_occurrence';

    public const SOURCE_CATEGORY_FALLBACK = 'category_fallback';

    public const SOURCE_CO_VIEW = 'co_view';

    protected $fillable = [
        'product_id',
        'related_product_id',
        'score',
        'source',
        'rank',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function relatedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }
}

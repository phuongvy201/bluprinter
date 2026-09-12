<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTemplate extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'user_id',
        'base_price',
        'list_price',
        'description',
        'media',
        'allow_customization',
        'customizations'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'list_price' => 'decimal:2',
        'media' => 'array',
        'allow_customization' => 'boolean',
        'customizations' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'template_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(TemplateAttribute::class, 'template_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(TemplateVariant::class, 'template_id');
    }

    // Customization Methods
    public function hasCustomization(): bool
    {
        return $this->allow_customization && !empty($this->customizations);
    }

    public function getCustomizationTypes(): array
    {
        return $this->customizations ?? [];
    }

    /**
     * Normalize admin template customization rows for storefront rendering.
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * Snapshot of template fields that should be copied onto a product at create time.
     *
     * @return array<string, mixed>
     */
    public function snapshotForProduct(): array
    {
        return [
            'category_id' => $this->category_id,
            'list_price' => $this->list_price,
            'description' => $this->description,
            'media' => $this->media,
            'allow_customization' => (bool) $this->allow_customization,
            'customizations' => $this->customizations,
        ];
    }

    /**
     * @param  array<int, mixed>|null  $items
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeCustomizationList(?array $items): array
    {
        if (empty($items) || ! is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item) {
            $type = $item['type'] ?? $item['input_type'] ?? 'text';
            $options = $item['options'] ?? [];

            if (is_string($options)) {
                $lines = preg_split('/\r\n|\r|\n/', $options) ?: [];
                $options = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
                $options = array_map(fn ($line) => ['label' => $line, 'value' => $line], $options);
            } elseif (is_array($options)) {
                $options = array_values(array_map(function ($option) {
                    if (is_array($option)) {
                        $label = $option['label'] ?? $option['value'] ?? '';
                        $value = $option['value'] ?? $option['label'] ?? '';

                        return ['label' => (string) $label, 'value' => (string) $value];
                    }

                    return ['label' => (string) $option, 'value' => (string) $option];
                }, $options));
            } else {
                $options = [];
            }

            return array_merge($item, [
                'type' => $type,
                'required' => filter_var($item['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'options' => $options,
            ]);
        }, $items));
    }

    public function getNormalizedCustomizations(): array
    {
        return static::normalizeCustomizationList(
            is_array($this->customizations) ? $this->customizations : null
        );
    }

    public function hasRequiredCustomizations(): bool
    {
        foreach ($this->getNormalizedCustomizations() as $customization) {
            if ($customization['required'] ?? false) {
                return true;
            }
        }

        return false;
    }

    public function getTotalCustomizationPrice(): float
    {
        if (!$this->hasCustomization()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($this->customizations as $customization) {
            $total += (float) ($customization['price'] ?? 0);
        }

        return $total;
    }

    public function getCustomizationByType(string $type): array
    {
        return array_filter($this->customizations ?? [], function ($customization) use ($type) {
            return ($customization['type'] ?? '') === $type;
        });
    }

    public function getRequiredCustomizations(): array
    {
        return array_filter($this->customizations ?? [], function ($customization) {
            return (bool) ($customization['required'] ?? false);
        });
    }
}

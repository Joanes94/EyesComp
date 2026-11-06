<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use BelongsToTenant, HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'base_unit',
        'purchase_price',
        'sale_price',
        'alert_threshold',
        'image_path',
        'description',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'integer',
        'sale_price' => 'integer',
        'alert_threshold' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_products')
            ->withPivot('sale_price', 'alert_threshold', 'is_active')
            ->withTimestamps();
    }

    /**
     * Obtenir le prix de vente effectif pour une boutique spécifique (F-PROD-05).
     */
    public function getPriceForStore(Store|int $store): int
    {
        $storeId = $store instanceof Store ? $store->id : $store;

        $storePivot = $this->storeProducts->firstWhere('store_id', $storeId);

        if ($storePivot && $storePivot->sale_price !== null) {
            return (int) $storePivot->sale_price;
        }

        return (int) $this->sale_price;
    }

    /**
     * Obtenir le seuil d'alerte de stock bas pour une boutique spécifique.
     */
    public function getAlertThresholdForStore(Store|int $store): int
    {
        $storeId = $store instanceof Store ? $store->id : $store;

        $storePivot = $this->storeProducts->firstWhere('store_id', $storeId);

        if ($storePivot && $storePivot->alert_threshold !== null) {
            return (int) $storePivot->alert_threshold;
        }

        return (int) $this->alert_threshold;
    }
}

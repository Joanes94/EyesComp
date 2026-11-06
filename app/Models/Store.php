<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use BelongsToTenant, HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'phone',
        'address',
        'logo_path',
        'currency',
        'receipt_header',
        'receipt_footer',
        'allow_negative_stock',
        'tax_enabled',
        'default_tax_rate',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'allow_negative_stock' => 'boolean',
        'tax_enabled' => 'boolean',
        'default_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_products')
            ->withPivot('sale_price', 'alert_threshold', 'is_active')
            ->withTimestamps();
    }
}

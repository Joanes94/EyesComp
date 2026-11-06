<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductUnit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion_factor',
        'sale_price',
        'barcode',
    ];

    protected $casts = [
        'conversion_factor' => 'integer',
        'sale_price' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

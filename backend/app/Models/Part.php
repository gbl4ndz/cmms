<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'part_number', 'description', 'unit',
        'unit_cost', 'quantity_on_hand', 'minimum_quantity', 'category_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost'        => 'decimal:2',
            'quantity_on_hand' => 'integer',
            'minimum_quantity' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function workOrderParts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_quantity;
    }
}

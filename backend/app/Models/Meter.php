<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_id', 'name', 'unit', 'frequency',
        'current_reading', 'last_maintenance_reading', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_reading'          => 'decimal:2',
            'last_maintenance_reading' => 'decimal:2',
            'frequency'                => 'integer',
            'is_active'                => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class)->latest('read_at');
    }

    public function latestReading(): HasMany
    {
        return $this->hasMany(MeterReading::class)->latest('read_at')->limit(1);
    }

    public function getUnitsSinceMaintenanceAttribute(): float
    {
        return (float) ($this->current_reading - $this->last_maintenance_reading);
    }

    public function isDueForMaintenance(): bool
    {
        return $this->units_since_maintenance >= $this->frequency;
    }

    public function getMaintenanceProgressAttribute(): float
    {
        if ($this->frequency <= 0) {
            return 0;
        }
        return min(100, round(($this->units_since_maintenance / $this->frequency) * 100, 1));
    }
}

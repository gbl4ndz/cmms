<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wo_number', 'title', 'description', 'status', 'priority', 'type',
        'asset_id', 'assigned_to', 'created_by',
        'due_date', 'started_at', 'completed_at',
        'estimated_hours', 'actual_hours',
    ];

    protected function casts(): array
    {
        return [
            'due_date'        => 'datetime',
            'started_at'      => 'datetime',
            'completed_at'    => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours'    => 'decimal:2',
        ];
    }

    // Valid status transition map
    public const STATUS_TRANSITIONS = [
        'open'        => ['in_progress'],
        'in_progress' => ['on_hold', 'closed'],
        'on_hold'     => ['in_progress', 'closed'],
        'closed'      => [],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(WorkOrderUpdate::class)->latest();
    }

    public function parts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function getTotalPartsCostAttribute(): float
    {
        return (float) $this->parts()->sum('total_cost');
    }
}

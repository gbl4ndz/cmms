<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Validation\ValidationException;

class MeterService
{
    public function recordReading(Meter $meter, float $value, ?string $notes, int $userId): MeterReading
    {
        if ($value < $meter->current_reading) {
            throw ValidationException::withMessages([
                'reading_value' => "Reading ({$value}) cannot be less than current reading ({$meter->current_reading}).",
            ]);
        }

        $reading = MeterReading::create([
            'meter_id'      => $meter->id,
            'recorded_by'   => $userId,
            'reading_value' => $value,
            'notes'         => $notes,
            'read_at'       => now(),
        ]);

        $meter->update(['current_reading' => $value]);

        return $reading->load('recordedBy');
    }

    public function resetMaintenanceBaseline(Meter $meter): Meter
    {
        $meter->update(['last_maintenance_reading' => $meter->current_reading]);
        return $meter->fresh();
    }
}

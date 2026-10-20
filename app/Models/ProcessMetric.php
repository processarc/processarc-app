<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMetric extends Model
{
    protected $fillable = ['process_id', 'cycle_time_hours', 'exception_count', 'measured_at'];

    protected function casts(): array
    {
        return ['cycle_time_hours' => 'decimal:1', 'measured_at' => 'date'];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}

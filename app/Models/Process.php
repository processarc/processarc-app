<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'status',
        'owner_id',
        'cycle_time_hours',
        'target_cycle_hours',
    ];

    protected function casts(): array
    {
        return [
            'cycle_time_hours' => 'decimal:1',
            'target_cycle_hours' => 'decimal:1',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('position');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(OptimizationOpportunity::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ProcessMetric::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'process_dependencies', 'process_id', 'depends_on_process_id');
    }

    public function recordMetricSnapshot(float $cycleTimeHours, int $exceptionCount): ProcessMetric
    {
        $measuredAt = now()->toDateString();
        $metric = $this->metrics()->whereDate('measured_at', $measuredAt)->first();
        $values = ['cycle_time_hours' => $cycleTimeHours, 'exception_count' => $exceptionCount];

        if ($metric) {
            $metric->update($values);

            return $metric;
        }

        return $this->metrics()->create($values + ['measured_at' => $measuredAt]);
    }
}

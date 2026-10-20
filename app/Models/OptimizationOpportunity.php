<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptimizationOpportunity extends Model
{
    protected $fillable = ['process_id', 'title', 'description', 'priority', 'status', 'owner_name', 'impact_hours'];

    protected function casts(): array
    {
        return ['impact_hours' => 'decimal:1'];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}

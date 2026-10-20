<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStage extends Model
{
    protected $fillable = ['process_id', 'name', 'owner_name', 'status', 'position', 'notes'];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}

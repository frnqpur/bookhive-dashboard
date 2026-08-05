<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoResetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'triggered_by',
        'trigger_type',
        'started_at',
        'finished_at',
        'status',
        'message',
        'summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'summary' => 'array',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

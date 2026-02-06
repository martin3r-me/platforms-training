<?php

namespace Platform\Training\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\ActivityLog\Traits\LogsActivity;
use Symfony\Component\Uid\UuidV7;

class Enrollment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'training_enrollments';

    protected $fillable = [
        'uuid',
        'training_session_id',
        'participant_id',
        'status',
        'notes',
        'enrolled_at',
        'team_id',
        'created_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                do {
                    $uuid = UuidV7::generate();
                } while (self::where('uuid', $uuid)->exists());
                $model->uuid = $uuid;
            }

            if (empty($model->enrolled_at)) {
                $model->enrolled_at = now();
            }
        });
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }
}

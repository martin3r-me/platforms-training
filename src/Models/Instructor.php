<?php

namespace Platform\Training\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Platform\ActivityLog\Traits\LogsActivity;
use Symfony\Component\Uid\UuidV7;

class Instructor extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'training_instructors';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'description',
        'is_active',
        'team_id',
        'created_by_user_id',
        'owned_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
        });
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'training_instructor', 'instructor_id', 'training_id');
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class, 'training_session_instructor', 'instructor_id', 'training_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owned_by_user_id');
    }
}

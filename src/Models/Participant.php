<?php

namespace Platform\Training\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Platform\ActivityLog\Traits\LogsActivity;
use Platform\Training\Traits\HasCrmContact;
use Symfony\Component\Uid\UuidV7;

class Participant extends Model
{
    use SoftDeletes, LogsActivity, HasCrmContact;

    protected $table = 'training_participants';

    protected $fillable = [
        'uuid',
        'hcm_employee_id',
        'notes',
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

    public function hcmEmployee(): BelongsTo
    {
        return $this->belongsTo(\Platform\Hcm\Models\HcmEmployee::class, 'hcm_employee_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'participant_id');
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

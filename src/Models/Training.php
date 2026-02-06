<?php

namespace Platform\Training\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Platform\ActivityLog\Traits\LogsActivity;
use Symfony\Component\Uid\UuidV7;

class Training extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'trainings';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'code',
        'group_id',
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

            if (empty($model->code)) {
                $model->code = static::generateCode($model->group_id, $model->team_id);
            }
        });
    }

    public static function generateCode(?int $groupId, ?int $teamId): string
    {
        $prefix = 'TRN';

        if ($groupId) {
            $group = TrainingGroup::find($groupId);
            if ($group && $group->code) {
                $prefix = $group->code;
            }
        }

        $lastCode = static::where('team_id', $teamId)
            ->where('code', 'like', $prefix . '-%')
            ->withTrashed()
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED) DESC")
            ->value('code');

        $nextNumber = 1;
        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'group_id');
    }

    /**
     * Trainings that must be completed before this one (prerequisites).
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'training_prerequisites',
            'training_id',
            'prerequisite_id'
        )->withTimestamps();
    }

    /**
     * Trainings that depend on this one (this training is a prerequisite for them).
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'training_prerequisites',
            'prerequisite_id',
            'training_id'
        )->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'training_id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'training_instructor', 'training_id', 'instructor_id');
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

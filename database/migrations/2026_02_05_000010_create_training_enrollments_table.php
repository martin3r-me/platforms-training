<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('training_session_id');
            $table->unsignedBigInteger('participant_id');
            $table->string('status')->default('registered');
            $table->text('notes')->nullable();
            $table->dateTime('enrolled_at')->nullable();

            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('training_session_id')->references('id')->on('training_sessions')->cascadeOnDelete();
            $table->foreign('participant_id')->references('id')->on('training_participants')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['training_session_id', 'participant_id'], 'enrollment_session_participant_unique');
            $table->index(['training_session_id', 'status']);
            $table->index('participant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_enrollments');
    }
};

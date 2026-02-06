<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_session_instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('training_session_id');
            $table->unsignedBigInteger('instructor_id');

            $table->foreign('training_session_id')->references('id')->on('training_sessions')->cascadeOnDelete();
            $table->foreign('instructor_id')->references('id')->on('training_instructors')->cascadeOnDelete();

            $table->unique(['training_session_id', 'instructor_id'], 'ts_instructor_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_session_instructor');
    }
};

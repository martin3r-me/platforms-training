<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('instructor_id');

            $table->foreign('training_id')->references('id')->on('trainings')->cascadeOnDelete();
            $table->foreign('instructor_id')->references('id')->on('training_instructors')->cascadeOnDelete();

            $table->unique(['training_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_instructor');
    }
};

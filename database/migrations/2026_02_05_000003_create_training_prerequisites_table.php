<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('prerequisite_id');
            $table->timestamps();

            $table->foreign('training_id')->references('id')->on('trainings')->cascadeOnDelete();
            $table->foreign('prerequisite_id')->references('id')->on('trainings')->cascadeOnDelete();

            $table->unique(['training_id', 'prerequisite_id'], 'training_prerequisite_unique');
            $table->index('training_id');
            $table->index('prerequisite_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_prerequisites');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_participants', function (Blueprint $table) {
            // Remove redundant fields - data comes from CRM contact via CrmContactLink
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
            $table->dropColumn(['name', 'email', 'phone', 'company']);

            // Optional HCM employee reference (source tracking)
            $table->unsignedBigInteger('hcm_employee_id')->nullable()->after('uuid');
            $table->foreign('hcm_employee_id')->references('id')->on('hcm_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_participants', function (Blueprint $table) {
            $table->dropForeign(['hcm_employee_id']);
            $table->dropColumn('hcm_employee_id');

            $table->string('name')->after('uuid');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('company')->nullable()->after('phone');

            $table->index('name');
            $table->index('email');
        });
    }
};

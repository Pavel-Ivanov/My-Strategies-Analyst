<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // User-level multi-tenancy

            $table->string('name');
            $table->timestamps();

            // Uniqueness per user (scoped to tenant)
            $table->unique(['user_id', 'name'], 'resources_user_name_unique');

            // Helpful tenant-first indexes for lookups
            $table->index('user_id', 'resources_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};

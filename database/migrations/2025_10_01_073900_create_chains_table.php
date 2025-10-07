<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chains', function (Blueprint $table): void {
            $table->id();
            // User-level multi-tenancy
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');

            $table->timestamps();

            // Tenant-first helpful indexes
            $table->index('user_id', 'chains_user_id_index');

            // Uniqueness within a user
            $table->unique(['user_id', 'name'], 'chains_user_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chains');
    }
};

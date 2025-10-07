<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // User-level multi-tenancy

            $table->string('asset_type')->default('coin');
            $table->string('name');
            $table->string('symbol');
            $table->foreignId('chain_id')->nullable()->constrained('chains')->nullOnDelete();
            $table->string('asset_contract_address')->nullable();
            $table->string('coingecko_asset_id')->nullable();
            $table->boolean('is_updatable')->default(false);

            $table->timestamps();

            // Uniqueness per user (scoped to tenant)
            $table->unique(['user_id', 'coingecko_asset_id'], 'assets_user_coingecko_unique');

            // Helpful tenant-first indexes for lookups
            $table->index(['user_id', 'symbol'], 'assets_user_symbol_index');
            $table->index(['user_id', 'name'], 'assets_user_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

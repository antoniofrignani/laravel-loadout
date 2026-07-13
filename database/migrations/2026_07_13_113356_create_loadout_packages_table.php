<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loadout_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loadout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_suggestion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->string('composer_name')->nullable();
            $table->string('npm_name')->nullable();
            $table->string('source_url')->nullable();
            $table->boolean('is_custom')->default(false)->index();
            $table->json('package_snapshot');
            $table->json('install_options')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['loadout_id', 'is_custom']);
            $table->index(['composer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loadout_packages');
    }
};

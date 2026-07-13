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
        Schema::create('loadouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('source_loadout_id')->nullable()->constrained('loadouts')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('visibility')->default('private')->index();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->json('builder_state')->nullable();
            $table->json('generation_snapshot')->nullable();
            $table->unsignedInteger('star_count')->default(0)->index();
            $table->unsignedInteger('clone_count')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'visibility']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loadouts');
    }
};

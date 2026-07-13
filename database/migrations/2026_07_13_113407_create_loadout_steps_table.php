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
        Schema::create('loadout_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loadout_id')->constrained()->cascadeOnDelete();
            $table->string('step_key');
            $table->unsignedTinyInteger('position')->default(1);
            $table->json('data');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['loadout_id', 'step_key']);
            $table->index(['loadout_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loadout_steps');
    }
};

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
        Schema::create('generated_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loadout_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('mime_type')->default('text/plain');
            $table->string('content_hash', 64);
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['loadout_id', 'filename']);
            $table->index(['loadout_id', 'content_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_artifacts');
    }
};

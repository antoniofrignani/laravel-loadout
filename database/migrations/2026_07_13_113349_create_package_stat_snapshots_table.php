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
        Schema::create('package_stat_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('source')->index();
            $table->unsignedInteger('stars')->nullable();
            $table->unsignedBigInteger('downloads')->nullable();
            $table->unsignedInteger('usage_count')->nullable();
            $table->timestamp('last_release_at')->nullable();
            $table->timestamp('last_source_update_at')->nullable();
            $table->timestamp('refreshed_at')->index();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['package_id', 'source', 'refreshed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_stat_snapshots');
    }
};

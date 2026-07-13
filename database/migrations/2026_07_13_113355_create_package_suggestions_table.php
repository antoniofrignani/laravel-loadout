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
        Schema::create('package_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('loadout_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('input');
            $table->string('display_name')->nullable();
            $table->string('composer_name')->nullable()->index();
            $table->string('npm_name')->nullable()->index();
            $table->string('source_url')->nullable();
            $table->string('packagist_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->json('lookup_payload')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_suggestions');
    }
};

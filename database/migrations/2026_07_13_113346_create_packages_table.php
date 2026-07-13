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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('composer_name')->nullable()->unique();
            $table->string('npm_name')->nullable()->index();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_url')->nullable();
            $table->string('packagist_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('docs_url')->nullable();
            $table->string('install_command')->nullable();
            $table->json('post_install_commands')->nullable();
            $table->json('publish_commands')->nullable();
            $table->json('migration_commands')->nullable();
            $table->json('required_services')->nullable();
            $table->json('laravel_version_constraints')->nullable();
            $table->json('php_version_constraints')->nullable();
            $table->json('tags')->nullable();
            $table->string('maintainer_name')->nullable();
            $table->string('status')->default('curated')->index();
            $table->boolean('is_official')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('stars')->default(0)->index();
            $table->unsignedBigInteger('downloads')->default(0)->index();
            $table->unsignedInteger('usage_count')->default(0)->index();
            $table->timestamp('last_release_at')->nullable()->index();
            $table->timestamp('last_source_update_at')->nullable()->index();
            $table->timestamp('stats_refreshed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

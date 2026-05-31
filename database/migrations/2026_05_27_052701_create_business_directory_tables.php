<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('directory_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#F1592A');
            $table->string('secondary_color', 7)->default('#1e293b');
            $table->string('tagline')->nullable();
            $table->string('announcement_title')->nullable();
            $table->text('announcement_body')->nullable();
            $table->string('announcement_link')->nullable();
            $table->boolean('announcement_active')->default(false);
            $table->boolean('announcement_dismissible')->default(true);
            $table->boolean('reviews_require_approval')->default(true);
            $table->timestamps();
        });

        Schema::create('directory_changelog_entries', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('title');
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->json('working_hours')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->date('featured_until')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('reviews_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('owner_deactivated')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index('city');
        });

        Schema::create('business_category', function (Blueprint $table) {
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('directory_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['business_id', 'directory_category_id']);
        });

        Schema::create('business_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('business_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('duration_text')->nullable();
            $table->string('price_text')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('price_text')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('business_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
        });

        Schema::create('business_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
        });

        Schema::create('product_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_product_id', 'user_id']);
        });

        Schema::create('business_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('thread_id');
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
            $table->index(['business_id', 'customer_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_messages');
        Schema::dropIfExists('product_likes');
        Schema::dropIfExists('business_likes');
        Schema::dropIfExists('business_reviews');
        Schema::dropIfExists('business_posts');
        Schema::dropIfExists('business_products');
        Schema::dropIfExists('business_services');
        Schema::dropIfExists('business_team_members');
        Schema::dropIfExists('business_category');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('directory_changelog_entries');
        Schema::dropIfExists('directory_settings');
        Schema::dropIfExists('directory_categories');
    }
};

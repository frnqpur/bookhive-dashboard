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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->string('ISBN_10', 20)->nullable()->unique();
            $table->string('ISBN_13', 20)->nullable()->unique();
            $table->string('author')->index();
            $table->string('category')->nullable()->index();
            $table->string('cover_image')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable()->index();
            $table->string('status')->default('published')->index();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_seeded')->default(false)->index();
            $table->boolean('is_protected')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

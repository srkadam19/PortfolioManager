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
        // Talent Table
        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->text('profile_image_url')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        // Employers Table
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade');
            $table->timestamps();
        });

        // Projects Table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->timestamps();
        });

        // Videos Table
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->timestamps();
        });

        // Social Links Table
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->text('url');
            $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade');
            $table->timestamps();
        });

        // Sections Table (New)
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            Schema::dropIfExists('sections');
            Schema::dropIfExists('social_links');
            Schema::dropIfExists('videos');
            Schema::dropIfExists('projects');
            Schema::dropIfExists('employers');
            Schema::dropIfExists('talents');
        });
    }
};

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
        Schema::table('talents', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('social_links')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talents', function (Blueprint $table) {
            //
        });
    }
};

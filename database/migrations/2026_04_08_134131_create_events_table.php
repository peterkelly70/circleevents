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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary');
            $table->text('description')->nullable();
            $table->string('venue_name');
            $table->string('venue_address')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone')->default('Australia/Perth');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('visibility')->default('public');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

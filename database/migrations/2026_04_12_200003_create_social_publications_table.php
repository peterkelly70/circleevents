<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // facebook, x, discord
            $table->unsignedBigInteger('target_account_id')->nullable();
            $table->string('status'); // pending, posted, failed
            $table->string('remote_post_id')->nullable();
            $table->string('remote_url')->nullable();
            $table->json('request_payload_json')->nullable();
            $table->json('response_payload_json')->nullable();
            $table->json('error_payload_json')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'platform']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_publications');
    }
};

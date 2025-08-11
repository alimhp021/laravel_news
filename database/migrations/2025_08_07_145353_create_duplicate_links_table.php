<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_links', function (Blueprint $table) {
            $table->id();
            // The duplicate message
            $table->foreignId('bare_message_id')->constrained('bare_messages')->onDelete('cascade');
            // The unique story it belongs to
            $table->foreignId('unique_news_id')->constrained('unique_news')->onDelete('cascade');
            $table->float('similarity_score'); // Store how similar it was
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_links');
    }
};
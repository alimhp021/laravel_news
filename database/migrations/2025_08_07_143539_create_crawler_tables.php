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
        // Create the table your crawler fills
        Schema::create('bare_messages', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name');
            $table->bigInteger('message_id')->unique();
            $table->text('message_text');
            $table->timestamp('message_timestamp', 0);
            $table->timestamp('crawled_at', 0)->useCurrent();
        });

        // Create the table your crawler uses to track its state
        Schema::create('channel_states', function (Blueprint $table) {
            $table->string('channel_name')->primary();
            $table->bigInteger('last_message_id')->default(0);
            $table->timestamp('updated_at', 0)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bare_messages');
        Schema::dropIfExists('channel_states');
    }
};
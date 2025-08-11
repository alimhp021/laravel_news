<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get the vector dimension from config
        $vectorDimension = config('services.huggingface.vector_dimension', 768);
        
        Schema::create('unique_news', function (Blueprint $table) use ($vectorDimension) {
            $table->id();
            $table->foreignId('source_message_id')->constrained('bare_messages')->onDelete('cascade');
            $table->text('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('original_text');
            $table->vector('embedding', $vectorDimension);
            $table->timestamps();
        });

        // Create vector index for similarity search
        DB::statement('CREATE INDEX ON unique_news USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('unique_news');
    }
};
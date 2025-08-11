<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bare_messages', function (Blueprint $table) {
            // This column will be null until the processor picks it up.
            $table->timestamp('processed_at')->nullable()->after('crawled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bare_messages', function (Blueprint $table) {
            $table->dropColumn('processed_at');
        });
    }
};

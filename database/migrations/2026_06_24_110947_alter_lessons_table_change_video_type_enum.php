<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE lessons MODIFY COLUMN video_type ENUM('url', 'upload', 'live') NOT NULL DEFAULT 'url'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE lessons MODIFY COLUMN video_type ENUM('url', 'upload') NOT NULL DEFAULT 'url'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE event_media MODIFY type ENUM('facebook_post','youtube_video','youtube_live','photo') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE event_media MODIFY type ENUM('facebook_post','youtube_video','youtube_live') NOT NULL");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('package')->nullable()->after('customer_phone');
            $table->unsignedInteger('guests')->nullable()->after('event_date');
            $table->string('event_location')->nullable()->after('guests');
            $table->enum('venue_type', ['salon', 'jardin'])->nullable()->after('event_location');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['package', 'guests', 'event_location', 'venue_type']);
        });
    }
};

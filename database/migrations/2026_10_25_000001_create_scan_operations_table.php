<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Monotonic, even when the CMS resets scan_count.
            $table->unsignedBigInteger('scan_version')->default(0);
        });

        Schema::create('scan_operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checkin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 16);
            $table->timestamps();
            $table->index(['guest_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_operations');
        Schema::table('guests', fn (Blueprint $table) => $table->dropColumn('scan_version'));
    }
};

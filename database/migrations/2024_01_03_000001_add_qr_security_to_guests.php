<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        Schema::table('guests', function (Blueprint $table) {
            $table->uuid('qr_code')->unique()->nullable()->after('id');
            $table->enum('scan_mode', ['unlimited', 'one_time', 'checkin_checkout', 'max_scans'])
                  ->default('unlimited')->after('is_active');
            $table->unsignedSmallInteger('max_scan_count')->default(2)->after('scan_mode');
            $table->unsignedInteger('scan_count')->default(0)->after('max_scan_count');
            $table->boolean('is_locked')->default(false)->after('scan_count');
        });

        // Generate UUID for existing guests
        foreach (\App\Models\Guest::all() as $guest) {
            $guest->updateQuietly(['qr_code' => Str::uuid()]);
        }

        // Make not nullable after seeding
        Schema::table('guests', function (Blueprint $table) {
            $table->uuid('qr_code')->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropUnique(['qr_code']);
            $table->dropColumn(['qr_code', 'scan_mode', 'max_scan_count', 'scan_count', 'is_locked']);
        });
    }
};

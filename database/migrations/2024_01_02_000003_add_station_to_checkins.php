<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('checkins', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations')->nullOnDelete()->after('guest_id');
        });
    }
    public function down(): void {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Station::class);
        });
    }
};

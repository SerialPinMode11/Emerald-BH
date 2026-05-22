<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->decimal('room_length_m', 8, 2)->nullable()->after('rejection_reason');
            $table->decimal('room_width_m', 8, 2)->nullable()->after('room_length_m');
            $table->decimal('room_height_m', 8, 2)->nullable()->after('room_width_m');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'room_length_m',
                'room_width_m',
                'room_height_m',
            ]);
        });
    }
};

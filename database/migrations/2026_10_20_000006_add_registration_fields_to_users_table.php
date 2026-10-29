<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Akun hasil pendaftaran publik menunggu persetujuan admin.
            $table->boolean('pending_approval')->default(false)->after('is_active');
            $table->text('application_note')->nullable()->after('pending_approval');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pending_approval', 'application_note']);
        });
    }
};

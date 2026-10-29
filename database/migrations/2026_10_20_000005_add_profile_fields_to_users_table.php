<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->after('name');
            $table->string('headline', 100)->nullable()->after('username');
            $table->text('bio')->nullable()->after('headline');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->string('website', 200)->nullable()->after('avatar_path');
            $table->string('instagram', 30)->nullable()->after('website');
            $table->string('linkedin', 200)->nullable()->after('instagram');
        });

        // Akun yang sudah ada diberi username otomatis dari namanya.
        $taken = [];
        DB::table('users')->orderBy('id')->get(['id', 'name', 'email'])->each(function ($user) use (&$taken) {
            $base = Str::limit(Str::slug($user->name ?: Str::before($user->email, '@'), '_'), 30, '');
            if (strlen($base) < 3) {
                $base = 'penulis';
            }

            $username = $base;
            $i = 2;
            while (in_array($username, $taken, true)) {
                $suffix = '_'.$i++;
                $username = Str::limit($base, 30 - strlen($suffix), '').$suffix;
            }

            $taken[] = $username;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'headline', 'bio', 'avatar_path', 'website', 'instagram', 'linkedin']);
        });
    }
};

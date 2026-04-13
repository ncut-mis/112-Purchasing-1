<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
        });

        $defaults = [
            [
                'name' => 'Admin One',
                'username' => 'admin1',
                'email' => 'admin1@gmail.com',
            ],
            [
                'name' => 'Admin Two',
                'username' => 'admin2',
                'email' => 'admin2@gmail.com',
            ],
        ];

        foreach ($defaults as $admin) {
            $existing = DB::table('admins')->where('username', $admin['username'])->first();

            if ($existing) {
                DB::table('admins')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $admin['name'],
                        'email' => $admin['email'],
                        'updated_at' => now(),
                    ]);
                continue;
            }

            DB::table('admins')->insert([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'username' => $admin['username'],
                'password' => Hash::make('admin12345'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};

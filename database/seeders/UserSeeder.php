<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'fullname' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => ROLE_ADMIN
        ]);

        DB::table('users')->insert([
            'fullname' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('123456'),
            'role' => ROLE_USER,

        ]);

        DB::table('users')->insert([
            'fullname' => 'Staff',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('123456'),
            'role' => ROLE_STAFF
        ]);

    }
}

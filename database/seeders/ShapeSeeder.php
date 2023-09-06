<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShapeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('shape')->insert([
            'name' => 'Browline',
        ]);
        DB::table('shape')->insert([
            'name' => 'Hình vuông',
        ]);
        DB::table('shape')->insert([
            'name' => 'Mắt mèo',
        ]);
        DB::table('shape')->insert([
            'name' => 'Oval',
        ]);
    }
}

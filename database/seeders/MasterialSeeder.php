<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('masterial')->insert([
            'name' => 'Acetate',
        ]);
        DB::table('masterial')->insert([
            'name' => 'Kim loại',
        ]);
        DB::table('masterial')->insert([
            'name' => 'Nhựa',
        ]);
        DB::table('masterial')->insert([
            'name' => 'Nhựa Dẻo',
        ]);
    }
}

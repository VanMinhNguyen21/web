<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use App\Models\XaPhuong;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\Supplier::factory(10)->create();
        $this->call([
            UserSeeder::class,
            ShapeSeeder::class,
            MasterialSeeder::class,
            TinhThanhPhoSeeder::class,
            QuanHuyenSeeder::class,
            XaPhuongSeeder::class,
        ]);
    }
}

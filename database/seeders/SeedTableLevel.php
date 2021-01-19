<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class SeedTableLevel extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Level::insert([
            [
                'code' => 'dkJBAW',
                'name' => 'Admin',
            ],
            [
                'code' => 'o9Sskr',
                'name' => 'Dokter',
            ],
            [
                'code' => 'mVXx3T',
                'name' => 'User',
            ],
        ]);
    }
}

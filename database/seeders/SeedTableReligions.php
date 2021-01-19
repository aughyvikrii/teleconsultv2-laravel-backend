<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Religion;

class SeedTableReligions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Religion::insert([
            [
                'name' => 'Islam'
            ],
            [
                'name' => 'Protestan'
            ],
            [
                'name' => 'Katolik'
            ],
            [
                'name' => 'Hindu'
            ],
            [
                'name' => 'Budha'
            ],
            [
                'name' => 'Konghucu'
            ],
            [
                'name' => 'Yahudi'
            ],
            [
                'name' => 'Kepercayaan'
            ],
            [
                'name' => 'Lainnya'
            ],
        ]);
    }
}

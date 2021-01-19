<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gender;

class SeedTableGenders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gender::insert([
            [
                'name' => 'Laki-Laki'
            ],
            [
                'name' => 'Perempuan'
            ],
            [
                'name' => 'Tidak Menyebutkan'
            ]
        ]);
    }
}

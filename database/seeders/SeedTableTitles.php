<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Title;

class SeedTableTitles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Title::insert([
            [
                'short' => 'By.',
                'name' => 'Bayi'
            ],
            [
                'short' => 'An.',
                'name' => 'Anak'
            ],
            [
                'short' => 'Tn.',
                'name' => 'Tuan'
            ],
            [
                'short' => 'Ny.',
                'name' => 'Nyona'
            ],
            [
                'short' => 'Sdr.',
                'name' => 'Laki-laki'
            ],
            [
                'short' => 'Nn.',
                'name' => 'Nona'
            ],
            [
                'short' => 'Alm.',
                'name' => 'Almarhum'
            ],
        ]);
    }
}

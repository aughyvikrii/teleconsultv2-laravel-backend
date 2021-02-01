<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Specialist;

class SeedTableSpecialists extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Specialist::create([
            'alt_name' => 'Spesialis Telinga Hidung Tenggorok Bedah Kepala Leher',
            'title' => 'Sp.THT-KL',
            'create_id' => 0
        ]);
    }
}

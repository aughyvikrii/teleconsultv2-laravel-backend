<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IdentityType;

class SeedTableIdentityType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IdentityType::insert([
            [
                'name' => 'KTP',
            ],
            [
                'name' => 'SIM',
            ],
            [
                'name' => 'PASPOR',
            ],
        ]);
    }
}

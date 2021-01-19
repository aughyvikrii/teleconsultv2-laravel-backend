<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarriedStatus;
use Illuminate\Support\Facades\Hash;

class SeedTableMarriedStatus extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MarriedStatus::insert([
            [
                'name' => 'Belum Menikah',
            ],
            [
                'name' => 'Menikah',
            ],
            [
                'name' => 'Janda',
            ],
            [
                'name' => 'Duda',
            ],
        ]);
    }
}

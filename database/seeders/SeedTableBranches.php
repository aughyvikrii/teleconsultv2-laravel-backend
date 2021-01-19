<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\{Branch};

class SeedTableBranches extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Branch::create([
            'code'  => 'EXP',
            'company' => 'Example Company',
            'name' => 'Example Branch',
            'create_id' => '0'
        ]);
    }
}

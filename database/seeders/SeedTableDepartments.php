<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Department;

class SeedTableDepartments extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create([
            'name' => 'THT & SYARAF',
            'description' => '-',
            'create_id' => 0
        ]);
    }
}

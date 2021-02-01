<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;

class SeedTablePersons extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Person::insert([
            [
                'uid' => '0',
                'first_name' => 'System',
                'last_name' => '',
                'phone_number' => 'xxx',
                'gid' => '1',
                'birth_place' => 'bandung',
                'birth_date' => date('Y-m-d'),
                'address' => '',
                'profile_pic' => null,
                'rid' => 9,
                'msid' => 1,
                'tid' => 3,
                'sid' => null,
            ],
            [
                'uid' => '1',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone_number' => 'xxx',
                'gid' => '1',
                'birth_place' => 'bandung',
                'birth_date' => date('Y-m-d'),
                'address' => '',
                'profile_pic' => null,
                'rid' => 9,
                'msid' => 1,
                'tid' => 3,
                'sid' => null,
            ],
            [
                'uid' => '2',
                'first_name' => 'Dokter',
                'last_name' => '',
                'phone_number' => 'xxx',
                'gid' => '1',
                'birth_place' => 'bandung',
                'birth_date' => date('Y-m-d'),
                'address' => '',
                'profile_pic' => null,
                'rid' => 9,
                'msid' => 1,
                'tid' => 3,
                'sid' => 1,
            ],
            [
                'uid' => '3',
                'first_name' => 'Pasien',
                'last_name' => '',
                'phone_number' => 'xxx',
                'gid' => '1',
                'birth_place' => 'bandung',
                'birth_date' => date('Y-m-d'),
                'address' => '',
                'profile_pic' => null,
                'rid' => 9,
                'msid' => 1,
                'tid' => 3,
                'sid' => null,
            ],
        ]);
    }
}

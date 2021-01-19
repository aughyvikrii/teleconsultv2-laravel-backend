<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SeedTableUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::create([
            'uid' => '0',
            'email' => 'system@gmail.com',
            'password' => Hash::make('system1234'),
            'phone_number' => '1234567890',
            'lid' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_code' => null
        ]);

        User::insert([
            [
                'email' => 'admin@gmail.com',
                'password' => Hash::make('1234'),
                'phone_number' => '1234567890',
                'lid' => 1,
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_code' => null
            ],
            [
                'email' => 'dokter@gmail.com',
                'password' => Hash::make('1234'),
                'phone_number' => '1234567890',
                'lid' => 2,
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_code' => null
            ],
            [
                'email' => 'user@gmail.com',
                'password' => Hash::make('1234'),
                'phone_number' => '1234567890',
                'lid' => 3,
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_code' => null
            ]
        ]);
    }
}

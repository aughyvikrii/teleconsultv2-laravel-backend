<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SeedTableLevel::class,
            SeedTableGenders::class,
            SeedTableIdentityType::class,
            SeedTableMarriedStatus::class,
            SeedTableReligions::class,
            SeedTableTitles::class,
            SeedTableUser::class,
        ]);

        $migrate = new SeedTableColumn;
        $migrate->up();

        $this->call([
            SeedTablePersons::class
        ]);
    }
}

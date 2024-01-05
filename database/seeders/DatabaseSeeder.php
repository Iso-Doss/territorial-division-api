<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(InternationalOrganisationSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(MunicipalitySeeder::class);
        $this->call(NeighborhoodSeeder::class);
        $this->call(ProvinceSeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(SubNeighborhoodSeeder::class);
        $this->call(UserSeeder::class);
    }
}

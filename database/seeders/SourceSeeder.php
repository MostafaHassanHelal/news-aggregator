<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sources = [
            [
                'name' => 'NewsAPI',
                'slug' => 'newsapi',
                'api_name' => 'newsapi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'the-guardian',
                'api_name' => 'guardian',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'New York Times',
                'slug' => 'new-york-times',
                'api_name' => 'nytimes',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sources')->insert($sources);
    }
}

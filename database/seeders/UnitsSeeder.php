<?php

namespace Database\Seeders;

use App\Models\SectionsModel;
use App\Models\UnitsModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $sections = SectionsModel::pluck('section_id')->toArray();

        if (empty($sections)) {
            echo "❌ No sections found. Run SectionsSeeder first.\n";
            return;
        }

        // Number of units per section
        $unitsPerSection = 2;

        $colors = [
            '#4CAF50',
            '#2196F3',
            '#FF9800',
            '#9C27B0',
            '#F44336',
            '#00BCD4',
        ];

        foreach ($sections as $sectionId) {

            for ($order = 1; $order <= $unitsPerSection; $order++) {

                UnitsModel::create([
                    'section_id' => $sectionId,
                    'title'      => "Unit $order: " . $faker->words(rand(2, 4), true),
                    'color'      => $faker->randomElement($colors),
                    'order'      => $order,
                    'is_last'    => $order === $unitsPerSection, // last unit per section
                ]);
            }
        }

        echo "✅ UnitsSeeder completed: {$unitsPerSection} units created per section with correct is_last flags.\n";
    }
}

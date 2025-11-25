<?php

namespace Database\Seeders;

use App\Models\BadgesModel;
use App\Models\User;
use App\Models\UserBadgesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Get all existing User IDs (assuming 'id' is the primary key for the User model)
        $userIds = User::pluck('id')->all();

        // 2. Get all existing Badge IDs (assuming 'badge_id' is the primary key for the BadgesModel)
        $badgeIds = BadgesModel::pluck('badge_id')->all();

        if (empty($userIds) || empty($badgeIds)) {
            $missing = empty($userIds) ? 'Users' : '';
            $missing .= (empty($userIds) && empty($badgeIds)) ? ' and ' : '';
            $missing .= empty($badgeIds) ? 'Badges' : '';
            echo "Skipping UserBadgesSeeder: No {$missing} found. Please seed the required tables first.\n";
            return;
        }

        $numUserBadgesToCreate = 100; // Total number of badge-user records to create

        // Check if records already exist to prevent duplicates if the model doesn't use a composite primary key
        // We will manually ensure unique combinations within the loop.

        $existingCombinations = [];

        for ($i = 0; $i < $numUserBadgesToCreate; $i++) {

            // Randomly select a user and a badge
            $userId = $faker->randomElement($userIds);
            $badgeId = $faker->randomElement($badgeIds);

            $combinationKey = "{$userId}-{$badgeId}";

            // Ensure we don't assign the same badge to the same user twice in this seeder run
            if (isset($existingCombinations[$combinationKey])) {
                continue; // Skip this iteration if the combination already exists
            }

            $existingCombinations[$combinationKey] = true;

            // Determine a random date the badge was earned, up to one year in the past
            $earnedAt = $faker->dateTimeBetween('-1 year', 'now');

            UserBadgesModel::create([
                'user_id'   => $userId,
                'badge_id'  => $badgeId,
                'earned_at' => $earnedAt,

                'created_at' => $earnedAt, // Use earned_at for created_at for historical accuracy
                'updated_at' => $earnedAt,
            ]);
        }

        echo "Successfully created " . count($existingCombinations) . " unique user badge records.\n";
    }
}

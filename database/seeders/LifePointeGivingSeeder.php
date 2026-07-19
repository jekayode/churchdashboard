<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ChurchProject;
use App\Models\GivingAccount;
use Illuminate\Database\Seeder;

/**
 * Giving accounts, current projects and the giving declaration, as shown on the
 * screens at LifePointe Greater Lekki.
 *
 * Re-running updates the existing rows rather than duplicating them.
 */
final class LifePointeGivingSeeder extends Seeder
{
    private const BRANCH_ID = 1;

    public function run(): void
    {
        $accounts = [
            [
                'account_name' => 'TEC/Life Pointe Greater Lekki',
                'account_number' => '0923177482',
                'bank_name' => 'GTBank',
                'purpose' => 'Tithe & Offering',
                'brand_color' => '#E8541E',
                'sort_order' => 1,
            ],
            [
                'account_name' => 'TEC/Life Pointe Gre Lekki S.EV',
                'account_number' => '0923177516',
                'bank_name' => 'GTBank',
                'purpose' => 'Special Events / Projects',
                'brand_color' => '#E8541E',
                'sort_order' => 2,
            ],
            [
                'account_name' => 'LifePointe Church GLK',
                'account_number' => '5017251433',
                'bank_name' => 'Moniepoint',
                'purpose' => 'Tithe & Offering',
                'brand_color' => '#0357EE',
                'sort_order' => 3,
            ],
            [
                'account_name' => 'The Elevation - LifePoint Church / GLK',
                'account_number' => '1973743640',
                'bank_name' => 'Access Bank',
                'purpose' => 'Tithe & Offering',
                'brand_color' => '#E85B25',
                'sort_order' => 4,
            ],
        ];

        foreach ($accounts as $account) {
            GivingAccount::updateOrCreate(
                ['branch_id' => self::BRANCH_ID, 'account_number' => $account['account_number']],
                $account + ['branch_id' => self::BRANCH_ID, 'is_active' => true],
            );
        }

        // Projects are given to through the Special Events account.
        $projectsAccount = GivingAccount::where('branch_id', self::BRANCH_ID)
            ->where('account_number', '0923177516')
            ->first();

        $projects = [
            ['name' => 'Children Worship Space', 'sort_order' => 1],
            ['name' => 'Cameras and Photography Gear', 'sort_order' => 2],
            ['name' => "Band's Monitors", 'sort_order' => 3],
            ['name' => 'Venue Transition', 'sort_order' => 4],
        ];

        foreach ($projects as $project) {
            ChurchProject::updateOrCreate(
                ['branch_id' => self::BRANCH_ID, 'name' => $project['name']],
                $project + [
                    'branch_id' => self::BRANCH_ID,
                    'period' => 'Q1 2026',
                    'giving_account_id' => $projectsAccount?->id,
                    'is_active' => true,
                ],
            );
        }

        Branch::where('id', self::BRANCH_ID)->update([
            'giving_declaration' => <<<'TEXT'
            Father, in the name of Jesus, I bring my tithe and offering to you today in gratitude for all you do for me and as an act of worship to you.

            As I give, I declare that I live a life of abundance. I live in great health, peace and joy. I do not lack anything good because you are my source. Your grace overflows to me, causing me to have sufficiency in all things and to abound in good works.

            I am a channel through which your blessings flow to the families of the earth. Every day of this week and in every way, I am blessed and highly favoured!
            TEXT,
        ]);
    }
}

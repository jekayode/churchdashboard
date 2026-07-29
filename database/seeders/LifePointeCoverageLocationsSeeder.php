<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CoverageLocation;
use Illuminate\Database\Seeder;

/**
 * The LifeGroup coverage areas members choose between as their closest location,
 * for LifePointe Greater Lekki. Re-running updates rather than duplicates.
 */
final class LifePointeCoverageLocationsSeeder extends Seeder
{
    private const BRANCH_ID = 1;

    public function run(): void
    {
        $locations = [
            'Ajah Badore - General Paint',
            'Hitech Road - Thera Annex',
            'The Patron Hotel - United Estate',
            'Monastery Road - Peace Garden',
            'Crown Estate - Majek/Genezland Hospital',
            'Kingdom Hall - GRA/Edidot Schools',
            'UBA - Ogunfayo',
            'Eputu - Bogije',
        ];

        foreach ($locations as $order => $name) {
            CoverageLocation::updateOrCreate(
                ['branch_id' => self::BRANCH_ID, 'name' => $name],
                ['sort_order' => $order + 1, 'is_active' => true],
            );
        }
    }
}

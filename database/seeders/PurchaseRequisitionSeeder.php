<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PurchaseRequisition;

class PurchaseRequisitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing
        PurchaseRequisition::truncate();

        $faker = \Faker\Factory::create();

        // Generate 100 records
        for ($i = 0; $i < 100; $i++) {
            $reqDate = Carbon::now()->subDays(rand(1, 60));
            
            // 70% chance of being processed (PO created)
            $hasPO = rand(1, 100) <= 70;
            
            $poDate = null;
            $poNumber = null;
            
            if ($hasPO) {
                // PO created 1-15 days after Req
                $daysLater = rand(1, 15);
                $poDate = $reqDate->copy()->addDays($daysLater);
                if ($poDate->isFuture()) {
                    $poDate = Carbon::now(); // Cap at now
                }
                $poNumber = '45000' . rand(10000, 99999);
            } else {
                // If no PO, check if it's "Overdue" (older than 14 days)
                // This happens naturally based on reqDate.
            }

            PurchaseRequisition::create([
                'pr_number' => '100' . sprintf('%05d', $i),
                'requisitioner' => $faker->firstName,
                'item_number' => '10',
                'short_text' => $faker->words(3, true),
                'po_number' => $poNumber,
                'deletion_flag' => null,
                'gr_indicator' => 'X',
                'ir_indicator' => 'X',
                'material' => 'MAT-' . rand(100, 999),
                'tracking_number' => 'TRK' . rand(1000, 9999),
                'purchasing_group' => 'PG' . rand(1, 5),
                'item_category' => 'L',
                'account_assignment' => 'K',
                'release_indicator' => 'R',
                'release_code' => '01',
                'release_date' => $reqDate->copy()->addDays(1),
                'purchasing_org' => '1000',
                'supplier' => $faker->company,
                'req_date' => $reqDate,
                'quantity' => rand(1, 100),
                'unit' => 'EA',
                'po_date' => $poDate,
                'po_time' => $hasPO ? '10:00:00' : null,
                'currency' => 'IDR',
                'total_value' => rand(1000000, 50000000),
            ]);
        }
    }
}

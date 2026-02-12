<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncSapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Purchase Requisition data from SAP (Simulated Download)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SAP Data Sync...');

        // Defined path for the sync file (Simulation)
        // In real world, this would download from SAP
        $path = storage_path('app/sap/daily_sync.xlsx');

        if (!file_exists($path)) {
            $this->warn("No sync file found at: $path");
            $this->info("Please place a file named 'daily_sync.xlsx' in 'storage/app/sap/' to simulate the sync.");
            return;
        }

        try {
            $this->info("Importing data from: $path");
            
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\PurchaseRequisitionImport, $path);
            
            $this->info('Sync completed successfully!');
            $this->info('Notifications have been dispatched for any PR-to-PO conversions.');

        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
        }
    }
}

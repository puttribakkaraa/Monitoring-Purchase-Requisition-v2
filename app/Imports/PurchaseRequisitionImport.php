<?php

namespace App\Imports;

use App\Models\PurchaseRequisition;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PurchaseRequisitionImport implements ToCollection
{
    /**
     * Mapping Purchasing Group Code → Department Name
     */
    const DEPT_MAP = [
        'DAA' => 'PPIC',
        'DAB' => 'PPIC',
        'DAC' => 'PPIC',
        'DAD' => 'Maint',
        'DAE' => 'Dieshop',
        'DAF' => 'OTHERS',
        'DAG' => 'Maint',
        'DAH' => 'OTHERS',
    ];

    public function collection(Collection $rows)
    {
        $headerRow = null;
        $headerIndex = 0;

        // 1. Find the Header Row
        foreach ($rows as $index => $row) {
            // Check known columns exist in this row
            // Normalize row values to lower case + underscores for check
            $values = $row->map(function($item) {
                return \Str::slug((string)$item, '_');
            })->toArray();

            // Keywords to identify header row
            if (in_array('purch_req', $values) || in_array('requisnr', $values) || in_array('pr_number', $values)) {
                $headerRow = $values;
                $headerIndex = $index;
                \Log::info("Found header at row index: $index");
                break;
            }
        }

        if (!$headerRow) {
            \Log::error("Header row not found in import file.");
            return;
        }

        // 2. Map Keys (Column Index -> Header Name)
        $columnMap = [];
        foreach ($headerRow as $colIndex => $slug) {
            if ($slug) {
                $columnMap[$colIndex] = $slug;
            }
        }

        // 3. Process Data Rows (Start after header)
        $dataRows = $rows->slice($headerIndex + 1);
        
        foreach ($dataRows as $row) {
            // Skip totally empty rows
            if ($row->filter()->isEmpty()) continue;

            // Map row data using column keys
            $mappedData = [];
            foreach ($columnMap as $colIndex => $key) {
                $mappedData[$key] = $row[$colIndex] ?? null;
            }

            $this->processRow($mappedData);
        }
    }

    private function processRow($row)
    {
        // Normalize keys
        $prNumber = $row['purch_req'] ?? $row['purchreq'] ?? $row['pr_number'] ?? $row['pr_no'] ?? null;
        $itemNumber = $row['item'] ?? $row['item_no'] ?? 10;

        // Skip invalid rows
        if (empty($prNumber) || !is_numeric($prNumber)) {
            return;
        }

        // Try to parse dates
        $reqDate = $this->parseDate($row['requisn_date'] ?? $row['req_date'] ?? $row['requisition_date'] ?? null);
        $poDate = $this->parseDate($row['po_date'] ?? $row['p_o_date'] ?? null);
        
        // Check for Status Change (Notification Trigger)
        // logic: If PR exists AND had NO PO, but NOW has PO -> Notify
        $poNumber = $row['po'] ?? $row['p_o'] ?? $row['po_number'] ?? null;
        
        if ($poNumber) {
            $existingPr = PurchaseRequisition::where('pr_number', $prNumber)
                ->where('item_number', $itemNumber)
                ->first();

            // If PR exists and previously had no PO
            if ($existingPr && empty($existingPr->po_number)) {
                // Trigger Notification
                $user = \App\Models\User::first();
                if ($user) {
                    $user->notify(new \App\Notifications\PrConvertedToPoNotification([
                        'pr_number' => $prNumber,
                        'po_number' => $poNumber,
                        'po_date' => $poDate ? $poDate->format('Y-m-d') : date('Y-m-d'),
                    ]));
                }
            }
        }

        // Map data
        $pr = PurchaseRequisition::updateOrCreate(
            [
                'pr_number' => $prNumber,
                'item_number' => $itemNumber,
            ],
            [
                'requisitioner' => $row['requisn_name'] ?? $row['requisitioner'] ?? $row['name_of_requisitioner'] ?? $row['requisnr'] ?? null,
                'short_text' => $row['short_text'] ?? $row['text'] ?? $row['description'] ?? null,
                'po_number' => $poNumber,
                'material' => $row['material'] ?? null,
                'purchasing_group' => $row['purch_grp'] ?? $row['purchgrp'] ?? $row['grp'] ?? $row['pgr'] ?? null,
                'purchasing_org' => $row['purch_org'] ?? $row['purchorg'] ?? $row['org'] ?? $row['porg'] ?? null,
                'supplier' => $row['supplier'] ?? $row['vendor'] ?? null,
                'req_date' => $reqDate,
                'po_date' => $poDate,
                'quantity' => $row['quantity'] ?? $row['qty'] ?? $row['quan'] ?? 0,
                'unit' => $row['unit'] ?? $row['uom'] ?? $row['un'] ?? null,
                'total_value' => $row['val_in_repor'] ?? $row['val_in_rep_cur'] ?? $row['total_value'] ?? $row['tot_value'] ?? 0,
                'currency' => $row['curr'] ?? $row['currency'] ?? $row['crcy'] ?? 'IDR',
                'tracking_number' => $row['tracking_number'] ?? $row['tracking_no'] ?? $row['trackingno'] ?? null,
            ]
        );

        // Auto-fill department from purchasing_group mapping
        $purchGroup = strtoupper(trim($pr->purchasing_group ?? ''));
        if ($purchGroup && isset(self::DEPT_MAP[$purchGroup])) {
            $pr->department = self::DEPT_MAP[$purchGroup];
            $pr->save();
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Excel Serial Date
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value);
            }
            // Standard parse
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}

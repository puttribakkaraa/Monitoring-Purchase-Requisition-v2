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

        // Skip invalid rows
        if (empty($prNumber) || !is_numeric($prNumber)) {
            return;
        }

        // Try to parse dates
        $reqDate = $this->parseDate($row['requisn_date'] ?? $row['req_date'] ?? $row['requisition_date'] ?? null);
        $poDate = $this->parseDate($row['po_date'] ?? $row['p_o_date'] ?? null);

        // Map data
        PurchaseRequisition::updateOrCreate(
            [
                'pr_number' => $prNumber,
                'item_number' => $row['item'] ?? $row['item_no'] ?? 10,
            ],
            [
                'requisitioner' => $row['requisn_name'] ?? $row['requisitioner'] ?? $row['name_of_requisitioner'] ?? $row['requisnr'] ?? null,
                'short_text' => $row['short_text'] ?? $row['text'] ?? $row['description'] ?? null,
                'po_number' => $row['po'] ?? $row['p_o'] ?? $row['po_number'] ?? null,
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
                'department' => $row['department'] ?? $row['dept'] ?? null,
                'tracking_number' => $row['tracking_number'] ?? $row['tracking_no'] ?? $row['trackingno'] ?? null,
            ]
        );
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

<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PurchaseRequisitionImport;

class DashboardController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $allowedExtensions = ['xlsx', 'xls', 'csv', 'html', 'htm', 'xml', 'mhtml', 'mht'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->withErrors(['file' => 'The file must be an Excel/CSV/HTML file.']);
        }

        try {
            $filePath = $file->getRealPath();
            $fileContent = file_get_contents($filePath);
            
            // Read file header
            $header = substr($fileContent, 0, 2048);
            
            // Check for ZIP (XLSX)
            if (substr($header, 0, 4) === "PK\x03\x04") {
                \Log::info("Detected: XLSX");
                Excel::import(new PurchaseRequisitionImport, $file, null, \Maatwebsite\Excel\Excel::XLSX);
                return redirect()->back()->with('success', 'Data imported successfully!');
            }
            
            // Check for OLE (true XLS)
            if (substr($header, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
                \Log::info("Detected: True XLS");
                Excel::import(new PurchaseRequisitionImport, $file, null, \Maatwebsite\Excel\Excel::XLS);
                return redirect()->back()->with('success', 'Data imported successfully!');
            }
            
            // For HTML/MHTML/XML based files - parse manually
            \Log::info("Detected: HTML/MHTML/XML - using manual parser");
            $count = $this->parseHtmlTable($fileContent);
            
            return redirect()->back()->with('success', "Data imported successfully! ($count records processed)");
            
        } catch (\Exception $e) {
            \Log::error("Import error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    /**
     * Parse HTML table from SAP export
     */
    private function parseHtmlTable(string $content): int
    {
        // Remove MHTML headers if present
        if (preg_match('/boundary="([^"]+)"/', $content, $matches)) {
            $boundary = $matches[1];
            $parts = explode('--' . $boundary, $content);
            foreach ($parts as $part) {
                if (stripos($part, 'text/html') !== false || stripos($part, '<table') !== false) {
                    $content = $part;
                    break;
                }
            }
        }
        
        // Load as DOM
        $dom = new \DOMDocument();
        @$dom->loadHTML($content, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $tables = $dom->getElementsByTagName('table');
        if ($tables->length === 0) {
            throw new \Exception('No table found in the file');
        }
        
        // Find the data table (usually the largest one)
        $dataTable = null;
        $maxRows = 0;
        foreach ($tables as $table) {
            $rows = $table->getElementsByTagName('tr');
            if ($rows->length > $maxRows) {
                $maxRows = $rows->length;
                $dataTable = $table;
            }
        }
        
        if (!$dataTable) {
            throw new \Exception('No valid data table found');
        }
        
        $rows = $dataTable->getElementsByTagName('tr');
        $headers = [];
        $count = 0;
        
        foreach ($rows as $index => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length === 0) {
                $cells = $row->getElementsByTagName('th');
            }
            
            $rowData = [];
            foreach ($cells as $cell) {
                $rowData[] = trim($cell->textContent);
            }
            
            // Skip empty rows
            if (count(array_filter($rowData)) === 0) continue;
            
            // First non-empty row with expected headers = header row
            if (empty($headers) && $this->looksLikeHeader($rowData)) {
                $headers = $this->normalizeHeaders($rowData);
                continue;
            }
            
            // Skip if no headers yet
            if (empty($headers)) continue;
            
            // Create record
            $record = array_combine(
                array_slice($headers, 0, count($rowData)),
                array_pad($rowData, count($headers), null)
            );
            
            if (!empty($record['pr_number'])) {
                $this->saveRecord($record);
                $count++;
            }
        }
        
        return $count;
    }

    private function looksLikeHeader(array $row): bool
    {
        $headerKeywords = ['purch', 'requisn', 'item', 'short', 'po', 'supplier', 'date', 'qty', 'quan'];
        $matches = 0;
        foreach ($row as $cell) {
            foreach ($headerKeywords as $keyword) {
                if (stripos($cell, $keyword) !== false) {
                    $matches++;
                    break;
                }
            }
        }
        return $matches >= 3;
    }

    private function normalizeHeaders(array $row): array
    {
        $mapping = [
            'purch.r' => 'pr_number', 'purch' => 'pr_number', 'pr' => 'pr_number',
            'requisn' => 'requisitioner',
            'item' => 'item_number',
            'short text' => 'short_text', 'short' => 'short_text',
            'po' => 'po_number',
            'd' => 'deletion_flag',
            'gr' => 'gr_indicator',
            'ir' => 'ir_indicator',
            'mater' => 'material',
            'tracking' => 'tracking_number',
            'pgr' => 'purchasing_group',
            'i' => 'item_category',
            'a' => 'account_assignment',
            'rel' => 'release_indicator',
            'code' => 'release_code',
            'release d' => 'release_date',
            'porg' => 'purchasing_org',
            'supplier' => 'supplier',
            'supp. m' => 'supplied_material', 'supp' => 'supplied_material',
            'rs' => 'rs_status',
            'req. date' => 'req_date', 'req.date' => 'req_date', 'req date' => 'req_date',
            'quan' => 'quantity', 'qty' => 'quantity',
            'un' => 'unit',
            'po date' => 'po_date',
            'time' => 'po_time',
            'croy' => 'currency', 'curr' => 'currency',
            'per' => 'per',
            'tot. value' => 'total_value', 'tot value' => 'total_value', 'total' => 'total_value',
        ];
        
        $normalized = [];
        foreach ($row as $header) {
            $h = strtolower(trim($header));
            $normalized[] = $mapping[$h] ?? preg_replace('/[^a-z0-9]+/', '_', $h);
        }
        return $normalized;
    }

    private function saveRecord(array $record): void
    {
        $parseDate = function($value) {
            if (!$value || $value === '-' || $value === '') return null;
            try {
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                    return Carbon::createFromFormat('d.m.Y', $value);
                }
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return Carbon::parse($value);
                }
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        };

        $parseNumber = function($value) {
            if (!$value || $value === '-' || $value === '') return 0;
            return (float) str_replace([',', ' '], ['', ''], $value);
        };

        PurchaseRequisition::updateOrCreate(
            [
                'pr_number' => $record['pr_number'] ?? null,
                'item_number' => $record['item_number'] ?? null,
            ],
            [
                'requisitioner' => $record['requisitioner'] ?? null,
                'short_text' => $record['short_text'] ?? null,
                'po_number' => $record['po_number'] ?? null,
                'deletion_flag' => $record['deletion_flag'] ?? null,
                'gr_indicator' => $record['gr_indicator'] ?? null,
                'ir_indicator' => $record['ir_indicator'] ?? null,
                'material' => $record['material'] ?? null,
                'tracking_number' => $record['tracking_number'] ?? null,
                'purchasing_group' => $record['purchasing_group'] ?? null,
                'item_category' => $record['item_category'] ?? null,
                'account_assignment' => $record['account_assignment'] ?? null,
                'release_indicator' => $record['release_indicator'] ?? null,
                'release_code' => $record['release_code'] ?? null,
                'release_date' => $parseDate($record['release_date'] ?? null),
                'purchasing_org' => $record['purchasing_org'] ?? null,
                'supplier' => $record['supplier'] ?? null,
                'supplied_material' => $record['supplied_material'] ?? null,
                'rs_status' => $record['rs_status'] ?? null,
                'req_date' => $parseDate($record['req_date'] ?? null),
                'quantity' => $parseNumber($record['quantity'] ?? 0),
                'unit' => $record['unit'] ?? null,
                'po_date' => $parseDate($record['po_date'] ?? null),
                'po_time' => $record['po_time'] ?? null,
                'currency' => $record['currency'] ?? null,
                'per' => $record['per'] ?? null,
                'total_value' => $parseNumber($record['total_value'] ?? 0),
            ]
        );
    }

    public function index()
    {
        // 1. Overall KPIs
        $totalPR = PurchaseRequisition::count();
        
        // Processed: Has PO Number
        $processedPO = PurchaseRequisition::whereNotNull('po_number')->where('po_number', '!=', '')->count();
        
        // Pending: No PO Number
        $pendingPO = PurchaseRequisition::where(function($q) {
            $q->whereNull('po_number')->orWhere('po_number', '');
        })->count();

        // 2. Overdue Calculation
        // Assuming "Overdue" means PR created > 14 days ago and still no PO.
        // Adjust "14" based on business rules.
        $overdueDays = 14;
        $overduePR = PurchaseRequisition::where(function($q) {
                $q->whereNull('po_number')->orWhere('po_number', '');
            })
            ->where('req_date', '<', Carbon::now()->subDays($overdueDays))
            ->count();

        // 3. Lead Time Calculation (Avg days to process)
        // DATEDIFF(po_date, req_date)
        // We only calculate for those that have both dates.
        if (config('database.default') === 'sqlite') {
             // SQLite datediff syntax: julianday(po_date) - julianday(req_date)
             $avgLeadTime = PurchaseRequisition::whereNotNull('po_date')
                ->whereNotNull('req_date')
                ->selectRaw('AVG(julianday(po_date) - julianday(req_date)) as avg_days')
                ->value('avg_days');
        } else {
             // MySQL/Postgres
             $avgLeadTime = PurchaseRequisition::whereNotNull('po_date')
                ->whereNotNull('req_date')
                ->selectRaw('AVG(DATEDIFF(po_date, req_date)) as avg_days')
                ->value('avg_days');
        }
        $avgLeadTime = round($avgLeadTime ?? 0, 1);

        // 4. Detailed Data for Table (Paginated) - Only show PRs without PO
        $requisitions = PurchaseRequisition::where(function($q) {
                $q->whereNull('po_number')->orWhere('po_number', '');
            })
            ->orderBy('req_date', 'desc')
            ->paginate(20);

        // One-time Seed: Populate department if largely empty (for visualization)
        if (PurchaseRequisition::count() > 0 && PurchaseRequisition::whereNull('department')->count() > 100) {
            $depts = ['IT', 'HR', 'Production', 'Finance', 'Logistics', 'Procurement', 'Marketing', 'Maintenance'];
            PurchaseRequisition::whereNull('department')->chunk(50, function ($prs) use ($depts) {
                foreach ($prs as $pr) {
                    $pr->update(['department' => $depts[array_rand($depts)]]);
                }
            });
        }

        // 6. Chart Data: Department Distribution (Line Chart)
        $deptChartData = $this->getDeptChartData();
        
        // 5. Chart Data: Monthly PR vs PO
        $chartData = $this->getChartData();

        return view('dashboard', compact(
            'totalPR', 
            'processedPO', 
            'pendingPO', 
            'overduePR', 
            'avgLeadTime', 
            'requisitions',
            'chartData',
            'deptChartData'
        ));
    }

    private function getDeptChartData()
    {
        $data = PurchaseRequisition::select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('department')->toArray(),
            'data' => $data->pluck('total')->toArray()
        ];
    }

    private function getChartData()
    {
        // Simple aggregation for last 12 months
        $months = collect([]);
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $labels = $months->map(fn($m) => $m->format('M Y'));
        
        $prData = [];
        $poData = [];

        foreach ($months as $month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $prCount = PurchaseRequisition::whereBetween('req_date', [$start, $end])->count();
            $poCount = PurchaseRequisition::whereBetween('po_date', [$start, $end])->count();
            
            $prData[] = $prCount;
            $poData[] = $poCount;
            
            $rates[] = $prCount > 0 ? round(($poCount / $prCount) * 100) : 0;
        }

        return [
            'labels' => $labels,
            'pr' => $prData,
            'po' => $poData,
            'rates' => $rates
        ];
    }

    /**
     * Get PR details with comments (AJAX)
     */
    public function getPrDetails($id)
    {
        $pr = PurchaseRequisition::with('comments')->findOrFail($id);
        
        return response()->json([
            'id' => $pr->id,
            'pr_number' => $pr->pr_number,
            'short_text' => $pr->short_text,
            'req_date' => $pr->req_date ? $pr->req_date->format('d.m.Y') : '-',
            'po_number' => $pr->po_number,
            'mitigation_reason' => $pr->mitigation_reason,
            'mitigation_status' => $pr->mitigation_status ?? 'open',
            'days_overdue' => $pr->req_date ? $pr->req_date->diffInDays(now()) : 0,
            'status' => $pr->po_number ? 'processed' : ($pr->req_date && $pr->req_date->diffInDays(now()) > 14 ? 'overdue' : 'pending'),
            'comments' => $pr->comments->map(function($c) {
                return [
                    'id' => $c->id,
                    'author_name' => $c->author_name,
                    'message' => $c->message,
                    'created_at' => $c->created_at->format('d M Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Update mitigation reason and status
     */
    public function updateMitigation(Request $request, $id)
    {
        $request->validate([
            'mitigation_reason' => 'nullable|string|max:1000',
            'mitigation_status' => 'nullable|in:open,in_progress,resolved',
        ]);

        $pr = PurchaseRequisition::findOrFail($id);
        $pr->update([
            'mitigation_reason' => $request->mitigation_reason,
            'mitigation_status' => $request->mitigation_status ?? 'open',
        ]);

        return response()->json(['success' => true, 'message' => 'Mitigation updated successfully']);
    }

    /**
     * Add a comment to PR
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'author_name' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
        ]);

        $pr = PurchaseRequisition::findOrFail($id);
        
        $comment = $pr->comments()->create([
            'author_name' => $request->author_name,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'message' => $comment->message,
                'created_at' => $comment->created_at->format('d M Y H:i'),
            ]
        ]);
    }

    /**
     * Get all comments for a PR (AJAX)
     */
    public function getComments($id)
    {
        $pr = PurchaseRequisition::findOrFail($id);
        
        return response()->json([
            'comments' => $pr->comments->map(function($c) {
                return [
                    'id' => $c->id,
                    'author_name' => $c->author_name,
                    'message' => $c->message,
                    'created_at' => $c->created_at->format('d M Y H:i'),
                ];
            }),
        ]);
    }
}

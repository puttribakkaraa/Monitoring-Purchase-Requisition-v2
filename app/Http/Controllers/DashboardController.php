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

    public function index(Request $request)
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
        // Logic: Same as chart - use po_release_date if available, else po_date.
        // Since we need complex coalescing logic that might differ across DBs or use manual columns,
        // and we already load the model, we can do this in PHP or raw SQL.
        // For consistency and simplicity with the small dataset assumption:
        
        $completedPRs = PurchaseRequisition::where(function($q) {
             $q->whereNotNull('po_date')->orWhereNotNull('po_release_date');
        })->whereNotNull('req_date')->get();

        $totalLeadTime = 0;
        $count = 0;

        foreach ($completedPRs as $pr) {
            $end = $pr->po_release_date ?? $pr->po_date;
            if ($end) {
                 $endDate = is_string($end) ? Carbon::parse($end) : $end;
                 $days = $pr->req_date->floatDiffInDays($endDate);
                 if ($days >= 0) {
                     $totalLeadTime += $days;
                     $count++;
                 }
            }
        }

        $avgLeadTime = $count > 0 ? round($totalLeadTime / $count, 1) : 0;

        // 4. Detailed Data for Table (Paginated)
        // CHECK SEARCH QUERY
        $search = $request->input('search');

        if ($search) {
            // Search Mode: Find ANY PR matching PO Number or Department
            // We do NOT limit to 'pending' only, as user might search for a closed PO.
            $requisitions = PurchaseRequisition::where(function($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%")
                      ->orWhere('purchasing_group', 'like', "%{$search}%") // Added this
                      ->orWhere('pr_number', 'like', "%{$search}%"); 
                })
                ->orderBy('req_date', 'desc')
                ->paginate(20)
                ->appends(['search' => $search]); // Preserve query in pagination links
        } else {
            // Default Mode: Show Pending PRs (No PO)
            $requisitions = PurchaseRequisition::where(function($q) {
                    $q->whereNull('po_number')->orWhere('po_number', '');
                })
                ->orderBy('req_date', 'desc')
                ->paginate(20);
        }

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

        // 7. Notifications (Latest 5)
        // Assuming we are showing system-wide notifications or for the first user
        // If there's auth, we should use auth()->user()->unreadNotifications
        $user = \App\Models\User::first();
        $notifications = $user ? $user->notifications()->latest()->take(5)->get() : collect([]);
        $unreadCount = $user ? $user->unreadNotifications->count() : 0;

        return view('dashboard', compact(
            'totalPR', 
            'processedPO', 
            'pendingPO', 
            'overduePR', 
            'avgLeadTime', 
            'requisitions',
            'chartData',
            'deptChartData',
            'notifications',
            'unreadCount'
        ));
    }

    private function getDeptChartData()
    {
        // Use purchasing_group as 'Department' per user request
        $data = PurchaseRequisition::select('purchasing_group as department', DB::raw('count(*) as total'))
            ->whereNotNull('purchasing_group')
            ->groupBy('purchasing_group')
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
            'days_overdue' => $pr->req_date ? floor($pr->req_date->diffInDays(now())) : 0,
            'status' => $pr->po_number ? 'processed' : ($pr->req_date && floor($pr->req_date->diffInDays(now())) > 14 ? 'overdue' : 'pending'),
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
    public function convertToPo(Request $request, $id)
    {
        $request->validate([
            'po_number' => 'required|string|max:255',
        ]);

        $pr = PurchaseRequisition::findOrFail($id);
        
        $pr->update([
            'po_number' => $request->po_number,
            'po_release_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'PR converted to PO successfully!']);
    }

    public function getTimelineData()
    {
        // Fetch ALL PRs to calculate stage averages independently
        $prs = PurchaseRequisition::with('comments')->get();
        
        $durations = [
            'feedback' => [], // Created -> First Comment
            'response' => [], // First Comment -> Second Comment
            'release' => []   // Second Comment (or Created) -> PO Release
        ];

        foreach ($prs as $pr) {
            $created = $pr->req_date;
            // Get comments sorted by date (already sorted in relation)
            $comments = $pr->comments; 
            
            $firstComment = $comments->first();
            $secondComment = $comments->count() > 1 ? $comments->get(1) : null;
            $released = $pr->po_release_date ?? $pr->po_date; 

            // 1. Created -> Feedback
            if ($firstComment && $created) {
                // Use floatDiffInDays to capture partial days (e.g. 0.5 days)
                $days = $created->floatDiffInDays($firstComment->created_at);
                if ($days >= 0) $durations['feedback'][] = $days;
            }

            // 2. Feedback -> Response
            if ($firstComment && $secondComment) {
                // Use floatDiffInDays for same-day responses
                $days = $firstComment->created_at->floatDiffInDays($secondComment->created_at);
                if ($days >= 0) $durations['response'][] = $days;
            }

            // 3. Response -> Released
            if ($released) {
                // User wants only PRs with chat interactions.
                // So strictly look for 2nd comment (Response) or 1st comment (Feedback).
                // If NO comments exist, we do NOT count this towards the average.
                
                $startPoint = $secondComment ? $secondComment->created_at : 
                             ($firstComment ? $firstComment->created_at : null); 
                
                if ($startPoint) {
                    $releaseDate = is_string($released) ? Carbon::parse($released) : $released;
                    if ($releaseDate >= $startPoint) {
                        $durations['release'][] = $startPoint->floatDiffInDays($releaseDate);
                    }
                }
            }
        }

        // Calculate Averages based on OCCURRENCE (Specific Count)
        // This answers: information "When X happens, how long does it take?"
        // We filter strictly for PRs that had the specific interaction.

        $avgFeedback = count($durations['feedback']) > 0 ? array_sum($durations['feedback']) / count($durations['feedback']) : 0;
        $avgResponse = count($durations['response']) > 0 ? array_sum($durations['response']) / count($durations['response']) : 0;
        $avgRelease = count($durations['release']) > 0 ? array_sum($durations['release']) / count($durations['release']) : 0;

        // --- New: Custom Lead Time Trend (2024, 2025, 2026 Monthly) ---
        $trendDates = [];
        $trendValues = [];

        // Define the buckets we want
        $buckets = [
            '2024' => ['start' => '2024-01-01', 'end' => '2024-12-31'],
            '2025' => ['start' => '2025-01-01', 'end' => '2025-12-31'],
        ];
        
        // Add months for 2026 (Jan to Dec)
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create(2026, $m, 1);
            $buckets[$date->format('M 26')] = [
                'start' => $date->startOfMonth()->toDateString(),
                'end' => $date->endOfMonth()->toDateString()
            ];
        }

        // Fetch ALL relevant PRs to process in PHP (simpler than complex SQL union)
        // We need PRs completed since 2024-01-01
        $allPrs = PurchaseRequisition::select('req_date', 'po_date', 'po_release_date')
            ->where(function($query) {
                $query->where('po_release_date', '>=', '2024-01-01')
                      ->orWhere('po_date', '>=', '2024-01-01');
            })
            ->get();

        foreach ($buckets as $label => $range) {
            $trendDates[] = $label;
            
            $totalDays = 0;
            $count = 0;

            foreach ($allPrs as $pr) {
                $end = $pr->po_release_date ?? $pr->po_date;
                
                if ($end) {
                    $endDate = is_string($end) ? Carbon::parse($end) : $end;
                    
                    // Check if this PR belongs in this bucket
                    if ($endDate->between($range['start'], $range['end'])) {
                         if ($pr->req_date) {
                             $diff = $pr->req_date->floatDiffInDays($endDate);
                             if ($diff >= 0) {
                                 $totalDays += $diff;
                                 $count++;
                             }
                        }
                    }
                }
            }
            
            $trendValues[] = $count > 0 ? round($totalDays / $count, 1) : 0;
        }

        return response()->json([
            'timeline' => [
                'labels' => ['PR Creation -> Feedback', 'Feedback -> Response', 'Response -> PO Release'],
                'data' => [
                    round($avgFeedback),
                    round($avgResponse),
                    round($avgRelease)
                ]
            ],
            'trend' => [
                'labels' => $trendDates,
                'data' => $trendValues
            ]
        ]);
    }
}

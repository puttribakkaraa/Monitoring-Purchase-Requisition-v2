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
        // Assuming "Overdue" means PR created > 20 days ago and still no PO.
        // Adjust "20" based on business rules.
        $overdueDays = 20;
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
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->latest()->take(5)->get() : collect([]);
        $unreadCount = $user ? $user->unreadNotifications->count() : 0;

        // 8. Department Performance Cards
        $picMap = self::getPicMap();
        $pgDescriptions = self::PG_DESCRIPTIONS;
        
        $deptPerformance = [];
        $totalAllPR = PurchaseRequisition::count();
        foreach (self::DEPARTMENTS as $dept) {
            $deptPRs = PurchaseRequisition::where('purchasing_group', $dept);
            $deptTotal = (clone $deptPRs)->count();
            $belumPO = (clone $deptPRs)->where(function($q) {
                $q->whereNull('po_number')->orWhere('po_number', '');
            });
            $deptQtyBelumPO = (clone $belumPO)->count();
            $deptAmountBelumPO = (clone $belumPO)->sum('total_value') ?: 0;
            $sudahFU = (clone $belumPO)->whereIn('feedback_status', ['waiting', 'responded'])->count();
            $deptPerformance[$dept] = [
                'pic'            => $picMap[$dept] ?? 'Unassigned',
                'total'          => $deptTotal,
                'qty'            => $deptQtyBelumPO,
                'percentage'     => $totalAllPR > 0 ? round(($deptTotal / $totalAllPR) * 100, 1) : 0,
                'amount'         => $deptAmountBelumPO,
                'released'       => (clone $deptPRs)->whereNotNull('po_number')->where('po_number', '!=', '')->count(),
                'sudah_fu'       => $sudahFU,
                'follow_up'      => $deptQtyBelumPO - $sudahFU,
                'need_feedback'  => (clone $deptPRs)->where('feedback_status', 'waiting')->count(),
                'sudah_feedback' => (clone $deptPRs)->where('feedback_status', 'responded')->count(),
            ];
        }

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
            'unreadCount',
            'deptPerformance',
            'picMap',
            'pgDescriptions'
        ));
    }

    /**
     * TV Monitoring Dashboard – fullscreen, dark, read-only.
     */
    public function tvDashboard()
    {
        $picMap = self::getPicMap();
        $pgDescriptions = self::PG_DESCRIPTIONS;

        $deptPerformance = [];
        $totalAllPR = PurchaseRequisition::count();
        foreach (self::DEPARTMENTS as $dept) {
            $deptPRs = PurchaseRequisition::where('purchasing_group', $dept);
            $deptTotal = (clone $deptPRs)->count();
            $belumPO = (clone $deptPRs)->where(function($q) {
                $q->whereNull('po_number')->orWhere('po_number', '');
            });
            $deptQtyBelumPO = (clone $belumPO)->count();
            $deptAmountBelumPO = (clone $belumPO)->sum('total_value') ?: 0;
            $sudahFU = (clone $belumPO)->whereIn('feedback_status', ['waiting', 'responded'])->count();
            $deptPerformance[$dept] = [
                'pic'            => $picMap[$dept] ?? 'Unassigned',
                'total'          => $deptTotal,
                'qty'            => $deptQtyBelumPO,
                'percentage'     => $totalAllPR > 0 ? round(($deptTotal / $totalAllPR) * 100, 1) : 0,
                'amount'         => $deptAmountBelumPO,
                'released'       => (clone $deptPRs)->whereNotNull('po_number')->where('po_number', '!=', '')->count(),
                'sudah_fu'       => $sudahFU,
                'follow_up'      => $deptQtyBelumPO - $sudahFU,
                'need_feedback'  => (clone $deptPRs)->where('feedback_status', 'waiting')->count(),
                'sudah_feedback' => (clone $deptPRs)->where('feedback_status', 'responded')->count(),
            ];
        }

        return view('tv_dashboard', compact(
            'deptPerformance',
            'picMap',
            'pgDescriptions'
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
            'po_date' => $pr->po_date ? $pr->po_date->setTimezone('Asia/Jakarta')->format('d M Y') : null,
            'po_release_date' => $pr->po_release_date ? (is_string($pr->po_release_date) ? $pr->po_release_date : $pr->po_release_date->setTimezone('Asia/Jakarta')->format('d M Y')) : null,
            'mitigation_reason' => $pr->mitigation_reason,
            'mitigation_status' => $pr->mitigation_status ?? 'open',
            'days_overdue' => $pr->req_date ? floor($pr->req_date->diffInDays(now())) : 0,
            'status' => $pr->po_number ? 'processed' : ($pr->req_date && floor($pr->req_date->diffInDays(now())) > 14 ? 'overdue' : 'pending'),
            'feedback_status' => $pr->feedback_status,
            'feedback_question' => $pr->feedback_question,
            'feedback_asked_at' => $pr->feedback_asked_at ? $pr->feedback_asked_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : null,
            'feedback_response' => $pr->feedback_response,
            'feedback_responded_at' => $pr->feedback_responded_at ? $pr->feedback_responded_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : null,
            'comments' => $pr->comments->map(function($c) {
                return [
                    'id' => $c->id,
                    'author_name' => $c->author_name,
                    'message' => $c->message,
                    'created_at' => $c->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
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

        // Trigger Notification
        $user = auth()->user();
        if ($user) {
            $user->notify(new \App\Notifications\PrConvertedToPoNotification([
                'pr_number' => $pr->pr_number,
                'po_number' => $request->po_number,
                'po_date' => now()->format('d M Y'),
            ]));
        }

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

    public function markAllRead()
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Admin asks department for feedback on a PR
     */
    public function askFeedback(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $pr = PurchaseRequisition::findOrFail($id);
        $pr->update([
            'feedback_status'    => 'waiting',
            'feedback_question'  => $request->question,
            'feedback_asked_at'  => now(),
            'feedback_response'  => null,
            'feedback_responded_at' => null,
            'mitigation_status'  => 'open',
        ]);

        // Add to chat history
        $userName = auth()->user()->name ?? 'Admin';
        $pr->comments()->create([
            'author_name' => $userName,
            'message'     => '[Pertanyaan Feedback] ' . $request->question,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dikirim ke departemen.',
        ]);
    }

    // ===== PIC MAPPING =====
    private static $picMapCache = null;

    public static function getPicMap()
    {
        if (self::$picMapCache === null) {
            self::$picMapCache = \App\Models\Department::pluck('pic_name', 'code')->toArray();
        }
        return self::$picMapCache;
    }

    public function updateDepartmentPic(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'dept_code' => 'required',
            'pic_name' => 'required|max:255'
        ]);

        \App\Models\Department::updateOrCreate(
            ['code' => $request->dept_code],
            ['pic_name' => $request->pic_name]
        );

        self::$picMapCache = null;

        return response()->json([
            'success' => true,
            'message' => 'PIC berhasil diupdate',
            'pic_name' => $request->pic_name
        ]);
    }

    const DEPARTMENTS = ['DAA','DAB','DAC','DAD','DAE','DAF','DAG','DAH'];

    const PG_DESCRIPTIONS = [
        'DAA' => 'Material',
        'DAB' => 'Komponen & Subcont',
        'DAC' => 'Cons, Lubricant & E-catalog',
        'DAD' => 'Spare part Local',
        'DAE' => 'Dies Regular & non Reg',
        'DAF' => 'Kebutuhan Umum semua Dept',
        'DAG' => 'Spare part Import',
        'DAH' => 'Building & Infra',
    ];

    /**
     * Smart Card Data API
     * Returns: status counts, department breakdown, PIC analytics, insights
     */
    public function getSmartCardData(Request $request)
    {
        $overdueDays = 20;
        $slaDays = 7;

        // === Build base query with filters ===
        $query = PurchaseRequisition::query();

        // Filter: Period
        if ($request->filled('date_from')) {
            $query->where('req_date', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('req_date', '<=', Carbon::parse($request->date_to)->endOfDay());
        }
        if ($request->filled('month')) {
            $m = Carbon::parse($request->month);
            $query->whereYear('req_date', $m->year)->whereMonth('req_date', $m->month);
        }

        // Filter: Department (purchasing_group)
        if ($request->filled('department')) {
            $query->where('purchasing_group', $request->department);
        }

        // Filter: PIC — expand to purchasing_groups
        if ($request->filled('pic')) {
            $picGroups = array_keys(array_filter(self::getPicMap(), fn($v) => $v === $request->pic));
            $query->whereIn('purchasing_group', $picGroups);
        }

        $allPRs = $query->get();
        $totalCount = $allPRs->count();

        // === Categorize each PR ===
        $categorized = [
            'released'  => collect(),
            'follow_up' => collect(),
            'feedback'  => collect(),
            'overdue'   => collect(),
            'no_status' => collect(),
        ];

        foreach ($allPRs as $pr) {
            $hasPO = !empty($pr->po_number);
            $age = $pr->req_date ? (int) $pr->req_date->diffInDays(now()) : 0;
            $pr->_age = $age;
            $pr->_pic = self::getPicMap()[$pr->purchasing_group] ?? 'Unassigned';

            if ($hasPO) {
                $categorized['released']->push($pr);
            } elseif ($pr->feedback_status === 'waiting') {
                $categorized['feedback']->push($pr);
            } elseif ($pr->feedback_status === 'responded') {
                $categorized['follow_up']->push($pr);
            } elseif ($age > $overdueDays) {
                $categorized['overdue']->push($pr);
            } else {
                $categorized['no_status']->push($pr);
            }
        }

        // === Status Card Summaries ===
        $statusCards = [];
        $releasedCount = $categorized['released']->count();
        $prOpenCount = $totalCount - $releasedCount;

        $statusCards['released'] = [
            'label' => 'Release',
            'count' => $releasedCount,
            'percentage' => $totalCount > 0 ? round(($releasedCount / $totalCount) * 100, 1) : 0,
            'color' => '#10b981',
            'icon' => 'ph-check-circle',
        ];

        $statusCards['pr_open'] = [
            'label' => 'PR Open',
            'count' => $prOpenCount,
            'percentage' => $totalCount > 0 ? round(($prOpenCount / $totalCount) * 100, 1) : 0,
            'color' => '#f59e0b',
            'icon' => 'ph-folder-open',
        ];

        // === Department Breakdown ===
        $departments = [];
        foreach (self::DEPARTMENTS as $dept) {
            $deptPRs = $allPRs->filter(fn($pr) => $pr->purchasing_group === $dept);
            $deptTotal = $deptPRs->count();
            if ($deptTotal === 0) {
                $departments[$dept] = $this->emptyDeptData($dept);
                continue;
            }

            $pic = self::getPicMap()[$dept] ?? 'Unassigned';

            // Basic counts
            $withPO = $deptPRs->filter(fn($pr) => !empty($pr->po_number));
            $withoutPO = $deptPRs->filter(fn($pr) => empty($pr->po_number));
            $overdue = $withoutPO->filter(fn($pr) => $pr->_age > $overdueDays);

            // Aging
            $ages = $withoutPO->pluck('_age');
            $avgAging = $ages->count() > 0 ? round($ages->avg(), 1) : 0;

            // Lead time (PR → PO) for completed ones
            $leadTimes = $withPO->map(function($pr) {
                $end = $pr->po_release_date ?? $pr->po_date;
                if ($end && $pr->req_date) {
                    $endDate = is_string($end) ? Carbon::parse($end) : $end;
                    return $pr->req_date->floatDiffInDays($endDate);
                }
                return null;
            })->filter()->values();
            $avgLeadTime = $leadTimes->count() > 0 ? round($leadTimes->avg(), 1) : 0;

            // Overdue rate
            $overdueRate = $deptTotal > 0 ? round(($overdue->count() / $deptTotal) * 100, 1) : 0;

            // Contribution to total
            $contribution = $totalCount > 0 ? round(($deptTotal / $totalCount) * 100, 1) : 0;

            // Aging distribution
            $agingDist = [
                'lte7'  => $withoutPO->filter(fn($pr) => $pr->_age <= 7)->count(),
                '8to14' => $withoutPO->filter(fn($pr) => $pr->_age >= 8 && $pr->_age <= 14)->count(),
                '15to30'=> $withoutPO->filter(fn($pr) => $pr->_age >= 15 && $pr->_age <= 30)->count(),
                'gt30'  => $withoutPO->filter(fn($pr) => $pr->_age > 30)->count(),
            ];

            // Workload
            $now = Carbon::now();
            $prActiveNow = $withoutPO->count();
            $prThisMonth = $deptPRs->filter(fn($pr) => $pr->req_date && $pr->req_date->month === $now->month && $pr->req_date->year === $now->year)->count();
            $prDoneThisMonth = $withPO->filter(function($pr) use ($now) {
                $poDate = $pr->po_date ?? $pr->po_release_date;
                if (!$poDate) return false;
                $d = is_string($poDate) ? Carbon::parse($poDate) : $poDate;
                return $d->month === $now->month && $d->year === $now->year;
            })->count();

            // SLA Compliance (PR selesai ≤ 7 hari)
            $slaCompliant = $withPO->filter(function($pr) use ($slaDays) {
                $end = $pr->po_release_date ?? $pr->po_date;
                if (!$end || !$pr->req_date) return false;
                $endDate = is_string($end) ? Carbon::parse($end) : $end;
                return $pr->req_date->diffInDays($endDate) <= $slaDays;
            })->count();
            $slaPercent = $withPO->count() > 0 ? round(($slaCompliant / $withPO->count()) * 100, 1) : 0;

            // Risk Score: 0-100
            $riskFactors = 0;
            $overdueCount = $overdue->count();
            $gt14 = $withoutPO->filter(fn($pr) => $pr->_age > 14)->count();
            if ($overdueCount > 5) $riskFactors += 35;
            elseif ($overdueCount > 2) $riskFactors += 20;
            elseif ($overdueCount > 0) $riskFactors += 10;
            if ($avgAging > 30) $riskFactors += 35;
            elseif ($avgAging > 14) $riskFactors += 20;
            elseif ($avgAging > 7) $riskFactors += 10;
            if ($gt14 > 5) $riskFactors += 30;
            elseif ($gt14 > 2) $riskFactors += 15;
            elseif ($gt14 > 0) $riskFactors += 5;

            $riskLevel = $riskFactors >= 60 ? 'high' : ($riskFactors >= 30 ? 'medium' : 'low');

            // Nominal
            $totalNominal = $deptPRs->sum('total_value');
            $nominalBelumPO = $withoutPO->sum('total_value');
            $nominalOverdue = $overdue->sum('total_value');
            $belumPoList = $withoutPO->sortByDesc('total_value')->map(fn($pr) => [
                'id' => $pr->id,
                'pr_number' => $pr->pr_number,
                'short_text' => $pr->short_text,
                'value' => $pr->total_value,
                'age' => $pr->_age,
            ])->values();

            // Status breakdown for this dept
            $deptStatusBreakdown = [];
            $statusKeys = ['follow_up', 'feedback', 'released', 'overdue', 'no_status'];
            foreach ($statusKeys as $sKey) {
                $deptStatusBreakdown[$sKey] = $categorized[$sKey]->filter(fn($pr) => $pr->purchasing_group === $dept)->count();
            }

            $actionReqPrs = $withoutPO->map(function($pr) {
                return [
                    'id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                    'short_text' => $pr->short_text,
                    'age' => $pr->_age,
                    'feedback_status' => $pr->feedback_status,
                    'date' => $pr->req_date ? $pr->req_date->format('d M Y') : '-',
                ];
            })->values();

            $departments[$dept] = [
                'department' => $dept,
                'pic' => $pic,
                'total' => $deptTotal,
                'with_po' => $withPO->count(),
                'without_po' => $withoutPO->count(),
                'overdue' => $overdueCount,
                'avg_aging' => $avgAging,
                'avg_lead_time' => $avgLeadTime,
                'overdue_rate' => $overdueRate,
                'contribution' => $contribution,
                'total_qty' => $deptPRs->sum('quantity'),
                'aging_distribution' => $agingDist,
                'workload' => [
                    'active' => $prActiveNow,
                    'incoming_this_month' => $prThisMonth,
                    'done_this_month' => $prDoneThisMonth,
                ],
                'sla' => [
                    'compliant' => $slaCompliant,
                    'total_completed' => $withPO->count(),
                    'percentage' => $slaPercent,
                ],
                'risk' => [
                    'score' => $riskFactors,
                    'level' => $riskLevel,
                ],
                'nominal' => [
                    'total' => $totalNominal,
                    'belum_po' => $nominalBelumPO,
                    'overdue' => $nominalOverdue,
                    'belum_po_list' => $belumPoList,
                ],
                'status_breakdown' => $deptStatusBreakdown,
                'action_req_prs' => $actionReqPrs,
            ];
        }

        // === Performance Insights (auto-generated) ===
        $insights = [];

        // PIC with most overdue
        $picOverdue = collect(self::getPicMap())->unique()->mapWithKeys(function($pic) use ($categorized) {
            return [$pic => $categorized['overdue']->filter(fn($pr) => $pr->_pic === $pic)->count()];
        })->sortDesc();
        if ($picOverdue->first() > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'ph-warning',
                'text' => "PIC dengan PR overdue terbanyak: <strong>{$picOverdue->keys()->first()}</strong> ({$picOverdue->first()} PR)",
            ];
        }

        // Dept with highest avg lead time
        $deptLeadTimes = collect($departments)->filter(fn($d) => $d['avg_lead_time'] > 0)->sortByDesc('avg_lead_time');
        if ($deptLeadTimes->isNotEmpty()) {
            $worst = $deptLeadTimes->first();
            $insights[] = [
                'type' => 'info',
                'icon' => 'ph-timer',
                'text' => "Rata-rata lead time tertinggi: <strong>{$worst['department']}</strong> ({$worst['avg_lead_time']} hari)",
            ];
        }

        // PIC with lowest SLA compliance
        $picSLA = collect(self::getPicMap())->unique()->mapWithKeys(function($pic) use ($departments) {
            $depts = array_keys(array_filter(self::getPicMap(), fn($v) => $v === $pic));
            $compliant = 0; $total = 0;
            foreach ($depts as $d) {
                if (isset($departments[$d])) {
                    $compliant += $departments[$d]['sla']['compliant'];
                    $total += $departments[$d]['sla']['total_completed'];
                }
            }
            return [$pic => $total > 0 ? round(($compliant / $total) * 100, 1) : null];
        })->filter()->sortBy(fn($v) => $v);

        if ($picSLA->isNotEmpty() && $picSLA->first() < 80) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'ph-chart-line-down',
                'text' => "SLA compliance terendah: <strong>{$picSLA->keys()->first()}</strong> ({$picSLA->first()}%)",
            ];
        }

        // Dept with most PR >14 days
        $deptGt14 = collect($departments)->mapWithKeys(fn($d, $k) => [$k => $d['aging_distribution']['15to30'] + $d['aging_distribution']['gt30']])->sortDesc();
        if ($deptGt14->first() > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'ph-clock-countdown',
                'text' => "Departemen dengan PR >14 hari terbanyak: <strong>{$deptGt14->keys()->first()}</strong> ({$deptGt14->first()} PR)",
            ];
        }

        // === PIC Rankings ===
        $picRankings = collect(self::getPicMap())->unique()->map(function($pic) use ($allPRs, $overdueDays) {
            $picPRs = $allPRs->filter(fn($pr) => $pr->_pic === $pic);
            return [
                'name' => $pic,
                'active' => $picPRs->filter(fn($pr) => empty($pr->po_number))->count(),
                'total' => $picPRs->count(),
            ];
        })->sortByDesc('active')->values();

        $belumPOCount = $allPRs->filter(fn($pr) => empty($pr->po_number))->count();
        $sudahFUCount = $allPRs->filter(fn($pr) => empty($pr->po_number) && in_array($pr->feedback_status, ['waiting', 'responded']))->count();
        
        $globalBreakdown = [
            'belum_po' => $belumPOCount,
            'released' => $statusCards['released']['count'],
            'sudah_fu' => $sudahFUCount,
            'follow_up'=> $belumPOCount - $sudahFUCount,
            'need_feedback' => $allPRs->where('feedback_status', 'waiting')->count(),
            'sudah_feedback' => $allPRs->where('feedback_status', 'responded')->count(),
        ];

        // Quick Alerts (Opsi 3)
        $quickAlerts = [
            'overdue' => $categorized['overdue']->count(), // Already calculated with $overdueDays (20)
            'feedback' => $categorized['feedback']->count(), // Waiting feedback
            'nominal_besar' => $allPRs->filter(fn($pr) => empty($pr->po_number) && $pr->total_value > 100000000)->count(), // Pending > 100M
            'belum_fu' => $belumPOCount - $sudahFUCount // Belum difollow up sama sekali
        ];

        return response()->json([
            'total' => $totalCount,
            'status_cards' => $statusCards,
            'departments' => $departments,
            'insights' => $insights,
            'global_breakdown' => $globalBreakdown,
            'quick_alerts' => $quickAlerts,
            'pic_rankings' => $picRankings,
            'filters' => [
                'departments' => self::DEPARTMENTS,
                'pics' => collect(self::getPicMap())->unique()->values(),
            ],
        ]);
    }

    private function emptyDeptData(string $dept): array
    {
        return [
            'department' => $dept,
            'pic' => self::getPicMap()[$dept] ?? 'Unassigned',
            'total' => 0, 'with_po' => 0, 'without_po' => 0, 'overdue' => 0,
            'avg_aging' => 0, 'avg_lead_time' => 0, 'overdue_rate' => 0,
            'contribution' => 0, 'total_qty' => 0,
            'aging_distribution' => ['lte7' => 0, '8to14' => 0, '15to30' => 0, 'gt30' => 0],
            'workload' => ['active' => 0, 'incoming_this_month' => 0, 'done_this_month' => 0],
            'sla' => ['compliant' => 0, 'total_completed' => 0, 'percentage' => 0],
            'risk' => ['score' => 0, 'level' => 'low'],
            'nominal' => ['total' => 0, 'belum_po' => 0, 'overdue' => 0, 'belum_po_list' => []],
            'status_breakdown' => ['released' => 0, 'follow_up' => 0, 'feedback' => 0, 'overdue' => 0, 'no_status' => 0],
            'action_req_prs' => [],
        ];
    }
}

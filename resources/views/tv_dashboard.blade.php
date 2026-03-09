<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TV Monitoring — Purchase Requisition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --accent-primary: #6366f1;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-primary: #6366f1;
            --border: #334155;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        .tv-container {
            padding: 1rem 1.5rem;
            max-width: 100%;
        }

        /* ===== HEADER BAR (same as admin) ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        .header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem;
            font-weight: 500; cursor: pointer; transition: all 0.2s;
            border: none; font-family: inherit;
        }
        .btn-outline {
            background: var(--bg-card); color: var(--text-primary);
            border: 1px solid var(--accent-primary);
        }
        .btn-outline:hover { background: var(--accent-primary); color: white; }
        .btn-theme-toggle {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.75rem; background: var(--bg-card);
            border: 1px solid var(--border); border-radius: 0.5rem;
            color: var(--text-secondary); font-size: 0.85rem;
            cursor: pointer; transition: all 0.2s; font-family: inherit;
        }
        .btn-theme-toggle:hover { border-color: var(--accent-primary); color: var(--text-primary); }

        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--accent-primary);
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.85rem; color: white; cursor: pointer;
        }

        /* Notification */
        .notification-wrapper { position: relative; cursor: pointer; }
        .notification-badge {
            position: absolute; top: -5px; right: -5px;
            background: var(--danger); color: white; font-size: 0.65rem;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; border: 2px solid var(--bg-body);
        }
        .notification-dropdown {
            position: absolute; top: 100%; right: 0; width: 320px;
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 1000; display: none; overflow: hidden; margin-top: 0.5rem;
        }
        .notification-header {
            padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);
            font-weight: 600; font-size: 0.9rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .notification-list { max-height: 300px; overflow-y: auto; }
        .notification-item {
            padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);
            display: flex; gap: 0.75rem; transition: background-color 0.2s;
        }
        .notification-item:hover { background: var(--bg-card-hover); }
        .notification-item.unread { background: rgba(99, 102, 241, 0.05); }
        .notification-empty {
            padding: 2rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;
        }

        /* User dropdown */
        .user-dropdown-menu {
            display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem;
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 0.75rem; width: 200px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 999; overflow: hidden;
        }

        /* LIVE indicator row */
        .live-row {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 1rem; margin-bottom: 0.75rem;
        }
        .live-badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981; font-size: 0.65rem; font-weight: 600;
            padding: 0.2rem 0.6rem; border-radius: 1rem; letter-spacing: 0.5px;
        }
        .live-dot {
            width: 6px; height: 6px; background: #10b981;
            border-radius: 50%; animation: pulse-dot 1.5s ease-in-out infinite;
        }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .clock { font-size: 0.85rem; color: var(--text-secondary); font-weight: 500; }

        /* ===== SUMMARY ROW ===== */
        .tv-summary-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: stretch;
        }

        /* KPI CARD (Release / PR Open) */
        .tv-kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            width: 170px;
            min-width: 170px;
        }
        .tv-kpi-card .kpi-title {
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .tv-kpi-card .kpi-donut {
            position: relative;
            width: 80px; height: 80px;
            border-radius: 50%;
            margin: 0.25rem 0;
        }
        .tv-kpi-card .kpi-donut .inner {
            position: absolute;
            top: 7px; left: 7px; right: 7px; bottom: 7px;
            background: var(--bg-card);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.1;
        }
        .tv-kpi-card .kpi-total {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-align: center;
        }
        .tv-kpi-card .kpi-total strong {
            font-size: 1rem;
            color: var(--text-primary);
            display: block;
            margin-top: 2px;
        }

        /* PIPELINE */
        .tv-pipeline {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0;
        }
        .tv-pipeline-card {
            flex: 1;
            background: var(--bg-card);
            border: 3px solid;
            border-radius: 12px;
            padding: 1rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 130px;
            position: relative;
        }
        .tv-pipeline-card .pipe-title { font-size: 0.8rem; font-weight: 700; margin-bottom: 0.5rem; }
        .tv-pipeline-card .pipe-val { font-size: 1.75rem; font-weight: 800; line-height: 1.1; margin-top: 0.4rem; }
        .tv-pipeline-card .pipe-kpi { font-size: 0.6rem; font-weight: 600; color: var(--text-secondary); letter-spacing: 0.5px; }
        .tv-pipeline-card .pipe-sub { font-size: 0.65rem; color: var(--text-secondary); margin-top: 0.4rem; }
        .tv-pipeline-arrow { display: flex; align-items: center; justify-content: center; width: 28px; }

        /* ===== DEPT GRID ===== */
        .tv-section-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .tv-dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
            gap: 0.75rem;
        }
        .tv-dept-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .tv-dept-card .dept-code { font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); }
        .tv-dept-card .dept-desc { font-size: 0.6rem; color: var(--text-secondary); font-weight: 500; }
        .tv-dept-card .dept-total-badge {
            font-size: 0.6rem; color: var(--text-secondary);
            background: rgba(255,255,255,0.05);
            padding: 0.1rem 0.4rem; border-radius: 1rem; border: 1px solid var(--border);
        }
        .tv-dept-card .dept-pic {
            font-size: 0.6rem; color: var(--text-secondary);
            display: flex; align-items: center; gap: 0.25rem;
            border-bottom: 1px dashed var(--border); padding-bottom: 0.4rem;
            width: 100%; justify-content: center;
        }
        .tv-dept-card .dept-donut {
            position: relative; width: 65px; height: 65px;
            border-radius: 50%; margin: 0.15rem 0;
        }
        .tv-dept-card .dept-donut .inner {
            position: absolute; top: 6px; left: 6px; right: 6px; bottom: 6px;
            background: var(--bg-card); border-radius: 50%;
            display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1;
        }
        .tv-dept-card .dept-amount { font-size: 0.55rem; color: var(--text-secondary); text-align: center; width: 100%; }
        .tv-dept-card .dept-bars { width: 100%; display: flex; flex-direction: column; gap: 0.3rem; padding: 0 0.15rem; }
        .tv-dept-card .bar-label { display: flex; justify-content: space-between; font-size: 0.5rem; margin-bottom: 0.1rem; }
        .tv-dept-card .bar-track {
            width: 100%; height: 3px; background: rgba(255,255,255,0.08);
            border-radius: 2px; position: relative; overflow: hidden;
        }
        .tv-dept-card .bar-fill { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 2px; }

        /* Refresh flash */
        .refresh-flash {
            position: fixed; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: flash-bar 1s ease-out forwards; z-index: 9999; pointer-events: none;
        }
        @keyframes flash-bar {
            0% { opacity: 1; transform: scaleX(0); transform-origin: left; }
            50% { opacity: 1; transform: scaleX(1); }
            100% { opacity: 0; transform: scaleX(1); }
        }
    </style>
</head>
<body>
    <div class="tv-container">

        <!-- ===== HEADER BAR (same as admin) ===== -->
        <div class="header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="{{ asset('images/logomtmfix.png') }}" alt="MTM Logo" style="height: 30px; width: auto; border-radius: 8px;">
                    <div>
                        <h1 style="margin:0; font-size: 1.5rem;">Monitoring Dashboard</h1>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin:0;">Real-time PR to PO conversion tracking</p>
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <!-- Theme Toggle -->
                <button class="btn-theme-toggle" onclick="toggleTheme()" title="Switch Theme">
                    <i class="ph ph-sun" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </button>

                <!-- Import Action -->
                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="file" name="file" id="fileInput" accept=".xlsx, .xls, .csv, .html, .mhtml" style="display: none;" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('fileInput').click()" class="btn btn-outline" title="Sync SAP Data">
                        <i class="ph ph-file-arrow-up" style="color: var(--success);"></i>
                    </button>
                </form>

                <!-- Requestor View Link -->
                <a href="{{ route('requestor.login') }}" class="btn btn-outline" title="Requestor View">
                    <i class="ph ph-users"></i>
                </a>

                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <!-- Notification Bell -->
                    <div class="notification-wrapper" onclick="toggleNotifications(event)">
                        <i class="ph ph-bell" style="font-size: 1.25rem; color: var(--text-secondary);"></i>
                    </div>

                    <!-- User Menu -->
                    <div style="position: relative;">
                        <div class="avatar" onclick="toggleUserMenu(event)" title="{{ Auth::user()->name ?? 'User' }}">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="user-dropdown-menu" id="userDropdown">
                            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);">
                                <div style="font-weight: 600; font-size: 0.85rem;">{{ Auth::user()->name ?? 'User' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">NPK: {{ Auth::user()->npk ?? '-' }}</div>
                            </div>
                            @if(Auth::user()->role === 'admin')
                            <a href="{{ route('users.index') }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; color: var(--text-primary); text-decoration: none; font-size: 0.85rem;" onmouseover="this.style.background='var(--bg-card-hover)'" onmouseout="this.style.background='transparent'">
                                <i class="ph ph-users-three"></i> Kelola User
                            </a>
                            @endif
                            <a href="javascript:void(0)" onclick="document.getElementById('profileModal').style.display='flex'" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; color: var(--text-primary); text-decoration: none; font-size: 0.85rem;" onmouseover="this.style.background='var(--bg-card-hover)'" onmouseout="this.style.background='transparent'">
                                <i class="ph ph-pencil-simple"></i> Edit Profile
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; border: none; background: none; color: #ef4444; font-family: inherit; font-size: 0.85rem; cursor: pointer;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">
                                    <i class="ph ph-sign-out"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
        <div style="padding: 0.6rem 1rem; border-radius: 0.5rem; margin-bottom: 0.75rem; background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid var(--success); font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Edit Profile Modal -->
        <div id="profileModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: var(--bg-card); border-radius: 1rem; width: 90%; max-width: 420px; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
                <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-user-gear" style="color: var(--accent-primary);"></i> Edit Profile
                    </h3>
                    <button onclick="document.getElementById('profileModal').style.display='none'" style="background: none; border: none; color: var(--text-secondary); font-size: 1.25rem; cursor: pointer;">
                        <i class="ph ph-x"></i>
                    </button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" style="padding: 1.25rem;">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.35rem;">NPK</label>
                        <input type="text" name="npk" value="{{ Auth::user()->npk }}" required
                            style="width: 100%; padding: 0.6rem 0.75rem; background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.85rem; outline: none;"
                            onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.35rem;">Password Baru <span style="font-size: 0.7rem;">(kosongkan jika tidak ingin ubah)</span></label>
                        <input type="password" name="password" placeholder="••••••"
                            style="width: 100%; padding: 0.6rem 0.75rem; background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.85rem; outline: none;"
                            onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    <button type="submit" style="width: 100%; padding: 0.65rem; background: var(--accent-primary); color: white; border: none; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; font-family: inherit;">
                        <i class="ph ph-floppy-disk"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- LIVE indicator row -->
        <div class="live-row">
            <div class="live-badge"><span class="live-dot"></span> LIVE</div>
            <div class="clock" id="tvClock">--:--:--</div>
        </div>

        <!-- TOP SUMMARY ROW -->
        <div class="tv-summary-row">
            <!-- RELEASE CARD -->
            <div class="tv-kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-title" style="color: #10b981;">
                    <i class="ph ph-check-circle"></i> Release
                </div>
                <div class="kpi-donut" id="tv_released_donut" style="background: conic-gradient(#10b981 0%, #334155 0);">
                    <div class="inner">
                        <span id="tv_released_pct" style="font-weight: 800; font-size: 1rem; color: #10b981;">0%</span>
                        <span style="font-size: 0.5rem; font-weight: 700; color: #10b981;">% PR</span>
                    </div>
                </div>
                <div class="kpi-total">Total PR: <strong id="tv_released_val">-</strong></div>
            </div>

            <!-- PR OPEN CARD -->
            <div class="tv-kpi-card" style="border-top: 3px solid #f59e0b;">
                <div class="kpi-title" style="color: #f59e0b;">
                    <i class="ph ph-folder-open"></i> PR Open
                </div>
                <div class="kpi-donut" id="tv_pr_open_donut" style="background: conic-gradient(#f59e0b 0%, #334155 0);">
                    <div class="inner">
                        <span id="tv_pr_open_pct" style="font-weight: 800; font-size: 1rem; color: #f59e0b;">0%</span>
                        <span style="font-size: 0.5rem; font-weight: 700; color: #f59e0b;">% PR</span>
                    </div>
                </div>
                <div class="kpi-total">Total PR: <strong id="tv_pr_open_val">-</strong></div>
            </div>

            <!-- VISUAL PIPELINE -->
            <div class="tv-pipeline">
                <div class="tv-pipeline-card" style="border-color: #0284c7;">
                    <div class="pipe-title">New Request</div>
                    <div style="position: relative;">
                        <i class="ph ph-file-text" style="color: #0284c7; font-size: 2rem;"></i>
                        <i class="ph-fill ph-plus-circle" style="color: #0284c7; font-size: 1rem; position: absolute; bottom: -2px; right: -8px; background: var(--bg-card); border-radius: 50%;"></i>
                    </div>
                    <div class="pipe-val" id="tv_vp_masuk" style="color: #0284c7;">-</div>
                    <div class="pipe-kpi">KPI</div>
                    <div class="pipe-sub">Antrean Belum PO</div>
                </div>
                <div class="tv-pipeline-arrow">
                    <svg viewBox="0 0 24 24" width="26" height="26" stroke="#0284c7" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </div>
                <div class="tv-pipeline-card" style="border-color: #fbd38d;">
                    <div class="pipe-title">In Process</div>
                    <i class="ph ph-gear-six" style="color: #f59e0b; font-size: 2rem;"></i>
                    <div class="pipe-val" id="tv_vp_proses" style="color: #f59e0b;">-</div>
                    <div class="pipe-kpi">KPI</div>
                    <div class="pipe-sub">F/U & Feedback</div>
                </div>
                <div class="tv-pipeline-arrow">
                    <svg viewBox="0 0 24 24" width="26" height="26" stroke="#fbd38d" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </div>
                <div class="tv-pipeline-card" style="border-color: #22c55e;">
                    <div class="pipe-title">Completed</div>
                    <div style="background: #22c55e; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-check" style="color: #fff; font-size: 1.6rem;"></i>
                    </div>
                    <div class="pipe-val" id="tv_vp_released" style="color: #22c55e;">-</div>
                    <div class="pipe-kpi">KPI</div>
                    <div class="pipe-sub">PO Released</div>
                </div>
            </div>
        </div>

        <!-- DEPARTMENT PERFORMANCE -->
        <div class="tv-section-title">
            <i class="ph ph-chart-bar" style="color: var(--accent-primary);"></i>
            Performance Outstanding PR yang Belum PO
        </div>

        <div class="tv-dept-grid">
            @foreach($deptPerformance as $deptCode => $perf)
            @php
                $val = $perf['percentage'];
                $circleColor = $val <= 3 ? '#10b981' : ($val <= 10 ? '#f59e0b' : '#ef4444');
                $total = max(1, $perf['total']);
                $bars = [
                    ['label' => 'Belum PO', 'val' => $perf['qty'], 'color' => '#ef4444'],
                    ['label' => 'PR Release', 'val' => $perf['released'], 'color' => '#10b981'],
                    ['label' => 'Sudah F/U', 'val' => $perf['sudah_fu'], 'color' => '#8b5cf6'],
                    ['label' => 'F/U Purchasing', 'val' => $perf['follow_up'], 'color' => '#f59e0b'],
                    ['label' => 'Need Feedback', 'val' => $perf['need_feedback'], 'color' => '#3b82f6'],
                    ['label' => 'Sudah Feedback', 'val' => $perf['sudah_feedback'], 'color' => '#06b6d4'],
                ];
            @endphp
            <div class="tv-dept-card">
                <div style="text-align: center; width: 100%;">
                    <div class="dept-code">{{ $deptCode }}</div>
                    <div class="dept-desc">{{ $pgDescriptions[$deptCode] ?? '' }}</div>
                    <span class="dept-total-badge">{{ $perf['total'] }} PR</span>
                </div>
                <div class="dept-pic"><i class="ph ph-user"></i> {{ $perf['pic'] }}</div>
                <div class="dept-donut" style="background: conic-gradient({{ $circleColor }} {{ $val }}%, #334155 0);">
                    <div class="inner">
                        <span style="font-weight: 800; font-size: 0.8rem; color: {{ $circleColor }};">{{ $val }}%</span>
                        <span style="font-size: 0.45rem; font-weight: 700; color: {{ $circleColor }};">% PR</span>
                    </div>
                </div>
                <div class="dept-amount">
                    Amount: <strong style="color: #f59e0b;">{{ number_format($perf['amount'] / 1000000, 1, ',', '.') }} Juta (Rp)</strong>
                </div>
                <div class="dept-bars">
                    @foreach($bars as $bar)
                    @php $pct = ($bar['val'] / $total) * 100; @endphp
                    <div>
                        <div class="bar-label">
                            <span style="color: var(--text-secondary);">{{ $bar['label'] }}</span>
                            <span style="font-weight: 700;">{{ $bar['val'] }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $pct }}%; background: {{ $bar['color'] }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        // === LIVE CLOCK ===
        function updateClock() {
            const now = new Date();
            const opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('tvClock').textContent = now.toLocaleTimeString('id-ID', opts) + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();

        // === LOAD SMART CARD DATA ===
        function loadTVData() {
            fetch('/api/smart-cards')
                .then(r => r.json())
                .then(data => {
                    const rel = data.status_cards?.released;
                    if (rel) {
                        document.getElementById('tv_released_val').textContent = rel.count.toLocaleString();
                        document.getElementById('tv_released_pct').textContent = rel.percentage + '%';
                        document.getElementById('tv_released_donut').style.background =
                            `conic-gradient(#10b981 ${rel.percentage}%, #334155 0)`;
                    }
                    const open = data.status_cards?.pr_open;
                    if (open) {
                        document.getElementById('tv_pr_open_val').textContent = open.count.toLocaleString();
                        document.getElementById('tv_pr_open_pct').textContent = open.percentage + '%';
                        document.getElementById('tv_pr_open_donut').style.background =
                            `conic-gradient(#f59e0b ${open.percentage}%, #334155 0)`;
                    }
                    if (data.global_breakdown) {
                        document.getElementById('tv_vp_masuk').textContent = data.global_breakdown.belum_po.toLocaleString();
                        document.getElementById('tv_vp_proses').textContent =
                            (data.global_breakdown.follow_up + data.global_breakdown.need_feedback + data.global_breakdown.sudah_feedback).toLocaleString();
                        document.getElementById('tv_vp_released').textContent = data.global_breakdown.released.toLocaleString();
                    }
                    // Flash bar indicator
                    const flash = document.createElement('div');
                    flash.className = 'refresh-flash';
                    document.body.appendChild(flash);
                    setTimeout(() => flash.remove(), 1200);
                })
                .catch(err => console.error('TV data error:', err));
        }

        // Load immediately
        loadTVData();

        // Auto-refresh data every 5 minutes
        setInterval(loadTVData, 300000);

        // Full page reload every 30 minutes for dept card data
        setInterval(() => { window.location.reload(); }, 1800000);

        // === THEME TOGGLE ===
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            html.setAttribute('data-theme', isDark ? 'light' : 'dark');
            document.getElementById('themeIcon').className = isDark ? 'ph ph-moon' : 'ph ph-sun';
            document.getElementById('themeText').textContent = isDark ? 'Light Mode' : 'Dark Mode';
            localStorage.setItem('tv-theme', isDark ? 'light' : 'dark');
        }

        // Restore saved theme
        (function() {
            const saved = localStorage.getItem('tv-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
                if (saved === 'light') {
                    document.getElementById('themeIcon').className = 'ph ph-moon';
                    document.getElementById('themeText').textContent = 'Light Mode';
                }
            }
        })();

        // === USER MENU TOGGLE ===
        function toggleUserMenu(e) {
            e.stopPropagation();
            const dd = document.getElementById('userDropdown');
            dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
        }

        function toggleNotifications(e) {
            e.stopPropagation();
            // No notification dropdown on TV view for now
        }

        document.addEventListener('click', function() {
            document.getElementById('userDropdown').style.display = 'none';
        });
    </script>
</body>
</html>

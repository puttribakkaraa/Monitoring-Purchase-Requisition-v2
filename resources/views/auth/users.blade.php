<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Monitoring PR</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root[data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #273548;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border: #334155;
            --accent-primary: #6366f1;
            --accent-hover: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        :root[data-theme="light"] {
            --bg-primary: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --accent-primary: #6366f1;
            --accent-hover: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4); }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: var(--bg-card-hover); }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.2); }

        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }

        /* Alert Messages */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--danger); }

        /* Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.875rem 1.5rem; text-align: left; }
        th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
        td { font-size: 0.9rem; border-bottom: 1px solid var(--border); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg-card-hover); }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-admin { background: rgba(99, 102, 241, 0.15); color: var(--accent-primary); }
        .badge-you { background: rgba(16, 185, 129, 0.15); color: var(--success); }

        /* Add User Form */
        .add-form {
            display: none;
            padding: 1.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            margin-bottom: 1.5rem;
        }

        .add-form.show { display: block; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.6rem 0.75rem;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            outline: none;
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
        }

        .validation-errors {
            color: var(--danger);
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }
        .validation-errors ul { list-style: none; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; gap: 1rem; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="ph ph-users-three"></i> User Management</h1>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-primary" onclick="document.querySelector('.add-form').classList.toggle('show')">
                    <i class="ph ph-plus"></i> Tambah User
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="ph ph-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="ph ph-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Add User Form -->
        <div class="add-form {{ $errors->any() ? 'show' : '' }}">
            @if($errors->any())
                <div class="validation-errors">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li><i class="ph ph-warning"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="npk">NPK</label>
                        <input type="text" name="npk" id="npk" placeholder="Contoh: 5678" value="{{ old('npk') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" name="name" id="name" placeholder="Nama lengkap" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Min. 6 karakter" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <i class="ph ph-list"></i> Daftar User ({{ count($users) }})
            </div>
            <table>
                <thead>
                    <tr>
                        <th>NPK</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td style="font-weight: 600;">{{ $user->npk ?? '-' }}</td>
                            <td>
                                {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="badge badge-you">You</span>
                                @endif
                            </td>
                            <td><span class="badge badge-admin">{{ ucfirst($user->role ?? 'admin') }}</span></td>
                            <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ $user->created_at->format('d M Y') }}</td>
                            <td style="text-align: center;">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('users.delete', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="ph ph-trash"></i> Hapus
                                        </button>
                                    </form>
                                @else
                                    <span style="color: var(--text-secondary); font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Apply saved theme
        const theme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    </script>
</body>
</html>

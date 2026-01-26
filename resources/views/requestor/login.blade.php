<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requestor Login - Monitoring PR</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --border: #334155;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            padding: 2.5rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .logo-text h1 {
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            background-color: #0f172a;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-main);
            font-size: 1rem;
            outline: none;
            cursor: pointer;
        }

        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .back-link {
            display: block;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .back-link:hover {
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <div class="logo-icon">
                <i class="ph ph-chart-polar"></i>
            </div>
            <div class="logo-text">
                <h1>Monitoring PR</h1>
                <p style="font-size: 0.75rem; color: var(--text-secondary); -webkit-text-fill-color: var(--text-secondary);">Requestor View</p>
            </div>
        </div>

        <form action="{{ route('requestor.auth') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="department">Select Your Department</label>
                <select name="department" id="department" required>
                    <option value="" disabled selected>Choose Department...</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-submit">
                Access Dashboard <i class="ph ph-arrow-right" style="vertical-align: middle; margin-left: 5px;"></i>
            </button>
        </form>

        <a href="{{ route('dashboard') }}" class="back-link">Back to Main Dashboard</a>
    </div>
</body>
</html>

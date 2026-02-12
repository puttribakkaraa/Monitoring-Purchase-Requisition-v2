<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requestor Login - Monitoring PR</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-body: #0f172a;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        body {
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.15) 0px, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            background-color: #ffffff;
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        /* Decorative top bar */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(to right, #6366f1, #10b981);
        }

        .logo-container {
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            max-width: 180px; 
            height: auto;
        }

        .app-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .app-title p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .select-wrapper {
            position: relative;
        }
        
        .select-wrapper::after {
            content: '\e9c5'; /* Phophos CaretDown */
            font-family: 'Phosphor';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-muted);
        }

        select {
            width: 100%;
            padding: 0.875rem 1rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            color: var(--text-dark);
            font-size: 1rem;
            outline: none;
            cursor: pointer;
            appearance: none;
            transition: all 0.2s;
        }

        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background-color: #fff;
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 2rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <!-- Ensure logo is large enough and unobstructed -->
            <img src="{{ asset('images/logomtmfix.png') }}" alt="MTM Logo" class="logo-img">
            <div class="app-title">
                <h1>Monitoring PR</h1>
                <p>Requestor Portal Access</p>
            </div>
        </div>

        <form action="{{ route('requestor.auth') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="department">Select Your Department</label>
                <div class="select-wrapper">
                    <select name="department" id="department" required>
                        <option value="" disabled selected>Choose Department...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Access Dashboard <i class="ph ph-arrow-right"></i>
            </button>
        </form>

        <a href="{{ route('dashboard') }}" class="back-link">
            <i class="ph ph-arrow-left"></i> Back to Main Dashboard
        </a>
    </div>
</body>
</html>

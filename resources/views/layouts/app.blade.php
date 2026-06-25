<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Commerce Analytics')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- Feather Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>

    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent-brand: #3b82f6;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-card: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 24px;
        }

        .sidebar-brand h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent-brand);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            background: #eff6ff;
            color: var(--accent-brand);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }

        .btn {
            padding: 8px 16px;
            background: var(--accent-brand);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .form-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: white;
            min-width: 150px;
        }
    </style>
    @yield('extra-styles')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i data-feather="shopping-cart"></i>
            <h1>ShopViz</h1>
        </div>
        <nav>
            <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                <i data-feather="pie-chart"></i> Dashboard
            </a>

            <a href="/insights" class="nav-link {{ request()->is('insights') ? 'active' : '' }}">
                <i data-feather="zap"></i> Insights
            </a>

        </nav>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>

    <script>
        feather.replace();
    </script>
    @yield('scripts')
</body>
</html>

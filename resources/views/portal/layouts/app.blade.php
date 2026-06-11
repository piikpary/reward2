<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reward Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar: #0d1b2a;
            --sidebar-dark: #08111d;
            --white: #ffffff;
            --black: #000000;
            --muted: #6b7280;
            --border: #eeeeee;
            --soft: #f7f8fa;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        * {
            box-sizing: border-box;
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--white);
            color: var(--black);
            font-size: 14px;
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        .topbar {
            height: 68px;
            background: var(--sidebar);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar-small {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .logout-btn {
            border: none;
            background: var(--white);
            color: var(--sidebar);
            padding: 11px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #f2f2f2;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: calc(100vh - 68px);
        }

        .sidebar {
            background: var(--sidebar);
            padding: 28px 18px;
            color: var(--white);
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--white);
            text-decoration: none;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 600;
            transition: 0.2s ease;
            border: 1px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--white);
            color: var(--sidebar);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
        }

        .nav-icon {
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
            font-size: 17px;
        }

        .content {
            background: var(--white);
            padding: 36px 44px;
            color: var(--black);
        }

        .page-title {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            color: var(--black);
            letter-spacing: -0.04em;
        }

        .page-subtitle {
            margin: 8px 0 28px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 400;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-body {
            padding: 28px;
        }

        .profile-card {
            max-width: 1100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 190px 1fr;
            gap: 22px;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--black);
        }

        .form-label .icon {
            color: var(--sidebar);
            font-size: 17px;
            width: 22px;
            text-align: center;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border: 1px solid #dcdcdc;
            border-radius: 12px;
            background: var(--white);
            color: var(--black);
            font-size: 14px;
            font-weight: 400;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--sidebar);
            box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.12);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        .card-footer {
            background: #fafafa;
            border-top: 1px solid var(--border);
            padding: 22px 28px;
        }

        .save-btn {
            border: none;
            background: var(--sidebar);
            color: var(--white);
            padding: 13px 22px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .save-btn:hover {
            background: var(--sidebar-dark);
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 500;
        }

        .field-wrap {
            width: 100%;
        }

        .dashboard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .date-pill {
            background: var(--soft);
            border: 1px solid var(--border);
            color: var(--black);
            padding: 11px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .dashboard-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: var(--shadow);
        }

        .dashboard-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--sidebar);
            color: var(--white);
            display: grid;
            place-items: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .dashboard-card h3 {
            margin: 0 0 10px;
            color: var(--black);
            font-size: 14px;
            font-weight: 600;
        }

        .dashboard-card h2 {
            margin: 0;
            color: var(--black);
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .dashboard-card p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 400;
            line-height: 1.6;
        }

        .dashboard-panel {
            margin-top: 24px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            box-shadow: var(--shadow);
        }

        .dashboard-panel h3 {
            margin: 0 0 10px;
            color: var(--black);
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .dashboard-panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
            font-weight: 400;
        }

        .quick-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .quick-btn {
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .quick-btn.primary {
            background: var(--sidebar);
            color: var(--white);
        }

        .quick-btn.secondary {
            background: var(--white);
            color: var(--black);
            border: 1px solid var(--border);
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding: 14px;
            }

            .nav {
                flex-direction: row;
                overflow-x: auto;
            }

            .nav-link {
                white-space: nowrap;
            }

            .content {
                padding: 24px 16px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0 14px;
            }

            .brand {
                font-size: 17px;
            }

            .avatar-small {
                display: none;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                display: block;
            }

            .date-pill {
                display: inline-flex;
                margin-top: 12px;
            }
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="brand">
        <div class="brand-icon">🎁</div>
        <span>Reward Portal</span>
    </div>

    @auth
        <div class="top-actions">
            <div class="avatar-small">👤</div>

            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>
    @endauth
</header>

<div class="layout">
    @auth
        <aside class="sidebar">
            <nav class="nav">
                <a href="{{ route('portal.dashboard') }}"
                   class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">▦</span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('portal.profile.edit') }}"
                   class="nav-link {{ request()->routeIs('portal.profile.*') ? 'active' : '' }}">
                    <span class="nav-icon">♙</span>
                    <span>Profile</span>
                </a>
                <a href="{{ route('portal.sliders.index') }}"
                    class="nav-link {{ request()->routeIs('portal.sliders.*') ? 'active' : '' }}">
                        <span class="nav-icon">▣</span>
                        <span>Sliders</span>
                    </a>

                <a href="{{ route('portal.spin-rewards.index') }}"
                    class="nav-link {{ request()->routeIs('portal.spin-rewards.*') ? 'active' : '' }}">
                        <span class="nav-icon">◉</span>
                        <span>Spin Rewards</span>
                    </a>

                <a href="{{ route('portal.customers.index') }}"
                    class="nav-link {{ request()->routeIs('portal.customers.*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span>
                        <span>Customers</span>
                </a>
                <a href="{{ route('portal.discounts.index') }}"
                    class="nav-link {{ request()->routeIs('portal.discounts.*') ? 'active' : '' }}">
                        <span class="nav-icon">%</span>
                        <span>Discounts</span>
                </a>
            </nav>
        </aside>
    @endauth

    <main class="content">
        @yield('content')
    </main>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Reward Portal</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

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
            position: sticky;
            top: 0;
            z-index: 20;
            height: 68px;
            padding: 0 28px;
            background: var(--sidebar);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
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
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
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
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .logout-btn {
            padding: 11px 18px;
            border: none;
            border-radius: 12px;
            background: var(--white);
            color: var(--sidebar);
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .logout-btn:hover {
            background: #f2f2f2;
        }

        .layout {
            min-height: calc(100vh - 68px);
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }

        .sidebar {
            min-height: calc(100vh - 68px);
            padding: 28px 18px;
            background: var(--sidebar);
            color: var(--white);
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-link {
            min-height: 56px;
            padding: 14px 16px;
            border: 1px solid transparent;
            border-radius: 16px;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s ease;
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
            flex: 0 0 26px;
            display: grid;
            place-items: center;
            font-size: 17px;
        }

        .nav-label {
            flex: 1;
        }

        .nav-arrow {
            margin-left: auto;
            font-size: 18px;
            line-height: 1;
            opacity: 0.65;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-submenu {
            position: relative;
            margin: 0 0 3px 29px;
            padding-left: 21px;
        }

        .nav-submenu::before {
            content: "";
            position: absolute;
            top: -4px;
            bottom: 18px;
            left: 7px;
            width: 1px;
            background: rgba(255, 255, 255, 0.18);
        }

        .nav-sub-link {
            position: relative;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 11px;
            color: rgba(255, 255, 255, 0.68);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .nav-sub-link::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.35);
            flex: 0 0 auto;
        }

        .nav-sub-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .nav-sub-link.active {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        .nav-sub-link.active::before {
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.12);
        }

        .content {
            min-width: 0;
            padding: 36px 44px;
            background: var(--white);
            color: var(--black);
        }

        .page-title {
            margin: 0;
            color: var(--black);
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .page-subtitle {
            margin: 8px 0 28px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 400;
        }

        .card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--white);
            box-shadow: var(--shadow);
        }

        .card-body {
            padding: 28px;
        }

        .profile-card {
            max-width: 1100px;
        }

        .form-row {
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 22px;
            align-items: center;
        }

        .form-label {
            color: var(--black);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .form-label .icon {
            width: 22px;
            color: var(--sidebar);
            font-size: 17px;
            text-align: center;
        }

        .field-wrap {
            width: 100%;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border: 1px solid #dcdcdc;
            border-radius: 12px;
            background: var(--white);
            color: var(--black);
            outline: none;
            font-size: 14px;
            font-weight: 400;
        }

        .form-control:focus {
            border-color: var(--sidebar);
            box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.12);
        }

        .divider {
            height: 1px;
            margin: 24px 0;
            background: var(--border);
        }

        .card-footer {
            padding: 22px 28px;
            border-top: 1px solid var(--border);
            background: #fafafa;
        }

        .save-btn {
            padding: 13px 22px;
            border: none;
            border-radius: 12px;
            background: var(--sidebar);
            color: var(--white);
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
        }

        .save-btn:hover {
            background: var(--sidebar-dark);
        }

        .error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }

        .success {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 500;
        }

        .dashboard-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .date-pill {
            padding: 11px 16px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--soft);
            color: var(--black);
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .dashboard-grid {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .dashboard-card {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--white);
            box-shadow: var(--shadow);
        }

        .dashboard-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 14px;
            border-radius: 14px;
            background: var(--sidebar);
            color: var(--white);
            display: grid;
            place-items: center;
            font-size: 20px;
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
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--white);
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
            font-size: 14px;
            font-weight: 400;
            line-height: 1.7;
        }

        .quick-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .quick-btn {
            padding: 12px 16px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .quick-btn.primary {
            background: var(--sidebar);
            color: var(--white);
        }

        .quick-btn.secondary {
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--black);
        }

        .pagination svg,
        .pagination nav svg,
        nav[role="navigation"] svg {
            width: 18px !important;
            height: 18px !important;
        }

        nav[role="navigation"] {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        nav[role="navigation"] a,
        nav[role="navigation"] span {
            font-size: 14px;
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
                min-height: auto;
                padding: 14px;
            }

            .nav {
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 4px;
            }

            .nav-group {
                flex: 0 0 auto;
            }

            .nav-link {
                white-space: nowrap;
            }

            .nav-submenu {
                display: none;
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
                margin-top: 12px;
                display: inline-flex;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<header class="topbar">
    <div class="brand">
        <div class="brand-icon">🎁</div>

        <span>Reward Portal</span>
    </div>

    @auth
        <div class="top-actions">
            <div class="avatar-small">
                👤
            </div>

            <form
                method="POST"
                action="{{ route('portal.logout') }}"
            >
                @csrf

                <button
                    class="logout-btn"
                    type="submit"
                >
                    Logout
                </button>
            </form>
        </div>
    @endauth
</header>

<div class="layout">
    @auth
        <aside class="sidebar">
            <nav class="nav">
                <a
                    href="{{ route('portal.dashboard') }}"
                    class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}"
                >
                    <span class="nav-icon">▦</span>
                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('portal.profile.edit') }}"
                    class="nav-link {{ request()->routeIs('portal.profile.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">♙</span>
                    <span>Profile</span>
                </a>

                <a
                    href="{{ route('portal.sliders.index') }}"
                    class="nav-link {{ request()->routeIs('portal.sliders.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">▣</span>
                    <span>Sliders</span>
                </a>

                <a
                    href="{{ route('portal.customers.index') }}"
                    class="nav-link {{ request()->routeIs('portal.customers.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">👥</span>
                    <span>Customers</span>
                </a>

                <a
                    href="{{ route('portal.discounts.index') }}"
                    class="nav-link {{ request()->routeIs('portal.discounts.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">%</span>
                    <span>Discount List</span>
                </a>

                <div class="nav-group">
                    <a
                        href="{{ route('portal.spin-campaigns.index') }}"
                        class="nav-link {{ request()->routeIs('portal.spin-campaigns*') ? 'active' : '' }}"
                    >
                        <span class="nav-icon">🎯</span>

                        <span class="nav-label">
                            Spin Campaigns
                        </span>

                        <span class="nav-arrow">
                            {{ request()->routeIs('portal.spin-campaigns*') ? '⌄' : '›' }}
                        </span>
                    </a>

                    @if(
                        isset($campaign)
                        && request()->routeIs(
                            'portal.spin-campaigns.sub-campaigns.*'
                        )
                    )
                        <div class="nav-submenu">
                            <a
                                href="{{ route(
                                    'portal.spin-campaigns.sub-campaigns.index',
                                    $campaign
                                ) }}"
                                class="nav-sub-link active"
                            >
                                <span>Subcampaigns</span>
                            </a>
                        </div>
                    @endif
                </div>
            </nav>
        </aside>
    @endauth

    <main class="content">
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
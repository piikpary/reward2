<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Share Campaigns')
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            color: #1f2937;
            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .topbar {
            background: #111827;
            color: white;
            padding: 16px 24px;
        }

        .topbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 700;
        }

        .topbar-links {
            display: flex;
            gap: 10px;
        }

        .topbar-links a {
            padding: 8px 12px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.12);
        }

        .container {
            width: min(1400px, calc(100% - 30px));
            margin: 25px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .page-title {
            margin: 0;
            font-size: 26px;
        }

        .page-description {
            margin: 7px 0 0;
            color: #6b7280;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            padding: 10px 12px;
            font-size: 14px;
            background: white;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input {
            width: auto;
        }

        .checkbox-item label {
            margin: 0;
        }

        .help {
            margin-top: 6px;
            color: #6b7280;
            font-size: 12px;
        }

        .error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .alert-success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 20px;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            border: 0;
            border-radius: 7px;
            padding: 9px 13px;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
        }

        .button-primary {
            color: white;
            background: #2563eb;
        }

        .button-secondary {
            color: #1f2937;
            background: #e5e7eb;
        }

        .button-success {
            color: white;
            background: #16a34a;
        }

        .button-warning {
            color: white;
            background: #d97706;
        }

        .button-danger {
            color: white;
            background: #dc2626;
        }

        .button-small {
            padding: 6px 9px;
            font-size: 12px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th,
        td {
            padding: 11px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: middle;
            font-size: 13px;
        }

        th {
            background: #f9fafb;
            color: #4b5563;
            font-weight: 700;
        }

        .poster {
            width: 70px;
            height: 55px;
            object-fit: cover;
            border-radius: 7px;
            border: 1px solid #e5e7eb;
        }

        .poster-large {
            width: 180px;
            height: 130px;
            object-fit: cover;
            border-radius: 9px;
            border: 1px solid #e5e7eb;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-success {
            color: #166534;
            background: #dcfce7;
        }

        .badge-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .badge-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .badge-gray {
            color: #374151;
            background: #e5e7eb;
        }

        .filter-row {
            display: grid;
            grid-template-columns:
                minmax(220px, 1fr)
                220px
                auto;
            gap: 10px;
            align-items: end;
        }

        .statistics {
            display: grid;
            grid-template-columns:
                repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 16px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 12px;
        }

        .stat-value {
            margin-top: 7px;
            font-size: 24px;
            font-weight: 700;
        }

        .pagination {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .muted {
            color: #6b7280;
        }

        .text-danger {
            color: #dc2626;
        }

        .text-success {
            color: #16a34a;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 900px) {
            .grid,
            .grid-3,
            .statistics {
                grid-template-columns: 1fr;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-title">
                Reward2 Campaign Portal
            </div>

            <div class="topbar-links">
                <a href="/portal">
                    Dashboard
                </a>

                <a
                    href="{{ route(
                        'portal.share-campaigns.index'
                    ) }}"
                >
                    Share Campaigns
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach (
                        $errors->all()
                        as $error
                    )
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
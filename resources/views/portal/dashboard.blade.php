@extends('portal.layouts.app')

@section('content')
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .page-title {
            margin: 0;
            font-size: 34px;
            font-weight: 900;
            color: #020617;
        }

        .page-subtitle {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 16px;
        }

        .date-pill {
            padding: 12px 20px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            color: #020617;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            white-space: nowrap;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-bottom: 22px;
        }

        .dashboard-card,
        .chart-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
        }

        .dashboard-card {
            padding: 28px;
            min-height: 180px;
        }

        .dashboard-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0b1b2b, #142f4a);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(11, 27, 43, 0.22);
        }

        .dashboard-card h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            color: #020617;
        }

        .dashboard-card h2 {
            margin: 12px 0 10px;
            font-size: 40px;
            line-height: 1;
            font-weight: 900;
            color: #020617;
        }

        .dashboard-card p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }

        .chart-card {
            padding: 30px;
            overflow: hidden;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 22px;
        }

        .chart-header h3 {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            color: #020617;
        }

        .chart-header p {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 15px;
        }

        .chart-badge {
            padding: 9px 15px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
        }

        .chart-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .chart-stat {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
        }

        .chart-stat span {
            display: block;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .chart-stat strong {
            display: block;
            color: #020617;
            font-size: 24px;
            font-weight: 900;
        }

        .line-chart-wrap {
            width: 100%;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #edf2f7;
            border-radius: 20px;
            padding: 18px 20px 14px;
        }

        .line-chart {
            width: 100%;
            height: 320px;
            display: block;
        }

        .chart-labels {
            display: flex;
            justify-content: space-between;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            margin-top: 10px;
        }

        @media (max-width: 900px) {
            .dashboard-grid,
            .chart-stats {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-title {
                font-size: 28px;
            }

            .chart-card {
                padding: 22px;
            }
        }
    </style>

    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Overview of your reward portal activity.</p>
        </div>

        <div class="date-pill">
            {{ now()->format('d M Y') }}
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-icon">👥</div>
            <h3>Total Users</h3>
            <h2>{{ $totalUsers ?? 0 }}</h2>
            <p>All registered portal and app users.</p>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-icon">♙</div>
            <h3>Total Customers</h3>
            <h2>{{ $totalCustomers ?? 0 }}</h2>
            <p>Customers registered by OTP login.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <div>
                <h3>Spin Activity</h3>
                <p>Overview of spin usage trend for this week.</p>
            </div>

            <div class="chart-badge">
                Weekly
            </div>
        </div>

        <div class="chart-stats">
            <div class="chart-stat">
                <span>Total Spins</span>
                <strong>98</strong>
            </div>

            <div class="chart-stat">
                <span>Total Discount</span>
                <strong>1,586.5%</strong>
            </div>

            <div class="chart-stat">
                <span>Active Campaign</span>
                <strong>1</strong>
            </div>
        </div>

        <div class="line-chart-wrap">
            <svg class="line-chart" viewBox="0 0 1000 320" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="lineFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#0b1b2b" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="#0b1b2b" stop-opacity="0" />
                    </linearGradient>
                </defs>

                <line x1="0" y1="270" x2="1000" y2="270" stroke="#e5e7eb" stroke-width="2" />
                <line x1="0" y1="210" x2="1000" y2="210" stroke="#eef2f7" stroke-width="2" />
                <line x1="0" y1="150" x2="1000" y2="150" stroke="#eef2f7" stroke-width="2" />
                <line x1="0" y1="90" x2="1000" y2="90" stroke="#eef2f7" stroke-width="2" />
                <line x1="0" y1="30" x2="1000" y2="30" stroke="#eef2f7" stroke-width="2" />

                <path
                    d="M 0 245 C 80 230, 110 215, 160 205 C 240 188, 280 215, 330 195 C 420 158, 445 105, 500 118 C 580 137, 620 160, 670 145 C 760 118, 790 55, 850 70 C 915 86, 950 102, 1000 92 L 1000 270 L 0 270 Z"
                    fill="url(#lineFill)"
                />

                <path
                    d="M 0 245 C 80 230, 110 215, 160 205 C 240 188, 280 215, 330 195 C 420 158, 445 105, 500 118 C 580 137, 620 160, 670 145 C 760 118, 790 55, 850 70 C 915 86, 950 102, 1000 92"
                    fill="none"
                    stroke="#0b1b2b"
                    stroke-width="8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />

                <circle cx="0" cy="245" r="8" fill="#0b1b2b" />
                <circle cx="160" cy="205" r="8" fill="#0b1b2b" />
                <circle cx="330" cy="195" r="8" fill="#0b1b2b" />
                <circle cx="500" cy="118" r="8" fill="#0b1b2b" />
                <circle cx="670" cy="145" r="8" fill="#0b1b2b" />
                <circle cx="850" cy="70" r="8" fill="#0b1b2b" />
                <circle cx="1000" cy="92" r="8" fill="#0b1b2b" />
            </svg>

            <div class="chart-labels">
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
                <span>Sun</span>
            </div>
        </div>
    </div>
@endsection
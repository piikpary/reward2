<style>
    .spin-campaign-page {
        max-width: 1500px;
        margin: 0 auto;
    }

    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }

    .campaign-header h1 {
        margin: 0;
        font-size: 34px;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: #111827;
    }

    .campaign-header p {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 15px;
        line-height: 1.6;
    }

    .campaign-guide-grid,
    .campaign-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .campaign-stats-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .guide-card,
    .stat-card {
        background: #ffffff;
        border: 1px solid #eef2f7;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .guide-card {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .guide-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .guide-icon.purple { background: #f3e8ff; color: #7c3aed; }
    .guide-icon.green { background: #dcfce7; color: #059669; }
    .guide-icon.blue { background: #dbeafe; color: #2563eb; }

    .guide-card strong,
    .stat-card strong {
        display: block;
        font-size: 22px;
        font-weight: 900;
        color: #111827;
    }

    .guide-card span,
    .stat-card span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .guide-card p {
        margin: 4px 0 0;
        color: #6b7280;
        line-height: 1.5;
    }

    .campaign-card {
        background: #ffffff;
        border: 1px solid #eef2f7;
        border-radius: 22px;
        padding: 24px;
        box-shadow: 0 16px 45px rgba(15, 23, 42, 0.08);
        margin-bottom: 22px;
    }

    .campaign-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 26px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        border: 1px solid #d9dee8;
        border-radius: 13px;
        padding: 13px 14px;
        font-size: 14px;
        outline: none;
        background: #ffffff;
        transition: 0.18s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #0d1b2a;
        box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.10);
    }

    .input-suffix {
        position: relative;
    }

    .input-suffix input {
        padding-right: 46px;
    }

    .input-suffix span {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-weight: 900;
    }

    .help-text {
        display: block;
        margin-top: 7px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.45;
    }

    .error {
        display: block;
        margin-top: 7px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 700;
    }

    .form-divider {
        height: 1px;
        background: #eef2f7;
        margin: 24px 0 16px;
    }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 12px 16px;
        border: 0;
        cursor: pointer;
        text-decoration: none;
        font-weight: 900;
        font-size: 14px;
        white-space: nowrap;
    }

    .btn-dark {
        background: #0d1b2a;
        color: #ffffff;
    }

    .btn-dark:hover {
        background: #08111d;
    }

    .btn-light {
        background: #f3f4f6;
        color: #111827;
    }

    .btn-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-purple {
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        color: #ffffff;
        box-shadow: 0 10px 24px rgba(124, 58, 237, 0.22);
    }

    .btn-sm {
        padding: 8px 11px;
        font-size: 12px;
        border-radius: 10px;
    }

    .success {
        background: #dcfce7;
        color: #047857;
        border: 1px solid #bbf7d0;
        padding: 13px 15px;
        border-radius: 14px;
        margin-bottom: 16px;
        font-weight: 800;
    }

    .rule-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .rule-box h3,
    .campaign-card h3 {
        margin: 0 0 10px;
        font-size: 20px;
        font-weight: 900;
        color: #111827;
    }

    .rule-box p {
        margin: 8px 0 0;
        color: #4b5563;
        line-height: 1.7;
    }

    .special-form {
        display: grid;
        grid-template-columns: 1fr 1fr 180px auto;
        gap: 14px;
        align-items: end;
    }

    .campaign-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .campaign-table th {
        background: #f9fafb;
        color: #374151;
        padding: 14px 12px;
        text-align: left;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e5e7eb;
    }

    .campaign-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .badge.active {
        background: #dcfce7;
        color: #047857;
    }

    .badge.inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .empty-state {
        text-align: center;
        padding: 35px 15px;
        color: #6b7280;
    }

    .inline-form {
        display: inline;
    }

    @media (max-width: 1100px) {
        .campaign-guide-grid,
        .campaign-stats-grid,
        .campaign-form-grid,
        .special-form {
            grid-template-columns: 1fr;
        }

        .campaign-header {
            flex-direction: column;
        }
    }
</style>
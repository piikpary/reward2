<style>
    .campaign-page {
        width: 100%;
    }

    .page-header {
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }

    .page-title {
        margin: 0;
        color: #000000;
        font-size: 30px;
        font-weight: 700;
        letter-spacing: -0.04em;
    }

    .page-description {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
    }

    .campaign-card {
        margin-bottom: 22px;
        padding: 24px;
        border: 1px solid #eeeeee;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .button {
        min-height: 42px;
        padding: 10px 16px;
        border: none;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
        transition: 0.2s ease;
    }

    .button:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .button-primary {
        background: #0d1b2a;
        color: #ffffff;
    }

    .button-primary:hover {
        background: #08111d;
        color: #ffffff;
    }

    .button-secondary {
        border: 1px solid #dddddd;
        background: #ffffff;
        color: #111827;
    }

    .button-secondary:hover {
        background: #f7f8fa;
        color: #111827;
    }

    .button-success {
        background: #16a34a;
        color: #ffffff;
    }

    .button-warning {
        background: #d97706;
        color: #ffffff;
    }

    .button-danger {
        background: #dc2626;
        color: #ffffff;
    }

    .button-small {
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 8px;
        font-size: 12px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 230px auto;
        gap: 14px;
        align-items: end;
    }

    .filter-group label,
    .field label {
        margin-bottom: 7px;
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }

    .filter-group input,
    .filter-group select,
    .field input,
    .field select,
    .field textarea {
        width: 100%;
        min-height: 46px;
        padding: 10px 14px;
        border: 1px solid #dcdcdc;
        border-radius: 11px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        outline: none;
        transition: 0.2s ease;
    }

    .field textarea {
        min-height: 130px;
        resize: vertical;
    }

    .filter-group input:focus,
    .filter-group select:focus,
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: #0d1b2a;
        box-shadow: 0 0 0 4px rgba(13, 27, 42, 0.1);
    }

    .actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .actions form {
        margin: 0;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .campaign-table {
        width: 100%;
        min-width: 1150px;
        border-collapse: collapse;
    }

    .campaign-table th,
    .campaign-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #eeeeee;
        text-align: left;
        vertical-align: middle;
        font-size: 13px;
        line-height: 1.5;
    }

    .campaign-table th {
        background: #f7f8fa;
        color: #374151;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    .campaign-table tbody tr:hover {
        background: #fafafa;
    }

    .campaign-table tbody tr:last-child td {
        border-bottom: none;
    }

    .poster {
        width: 76px;
        height: 58px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        object-fit: cover;
        background: #f7f8fa;
    }

    .poster-large {
        width: 190px;
        height: 145px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        object-fit: cover;
        background: #f7f8fa;
    }

    .badge {
        padding: 5px 9px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-gray {
        background: #e5e7eb;
        color: #374151;
    }

    .muted {
        color: #6b7280;
        font-size: 12px;
    }

    .empty-state {
        padding: 35px 20px !important;
        color: #6b7280;
        text-align: center !important;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .help {
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.5;
    }

    .error {
        margin-top: 6px;
        color: #dc2626;
        font-size: 12px;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 22px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input {
        width: 18px;
        min-height: 18px;
    }

    .checkbox-item label {
        margin: 0;
    }

    .form-footer {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #eeeeee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .statistics {
        margin-bottom: 22px;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .stat {
        padding: 20px;
        border: 1px solid #eeeeee;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }

    .stat-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .stat-value {
        margin-top: 8px;
        color: #111827;
        font-size: 28px;
        font-weight: 800;
    }

    .pagination-area {
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    @media (max-width: 1100px) {
        .statistics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .page-header {
            flex-direction: column;
        }

        .filter-row,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .field-full {
            grid-column: auto;
        }

        .statistics {
            grid-template-columns: 1fr;
        }

        .pagination-area {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
<style>
    .announcement-page {
        padding: 8px 0 40px;
    }

    .announcement-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .announcement-header h1 {
        margin: 0;
        color: #071629;
        font-size: 32px;
        font-weight: 900;
    }

    .announcement-header p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 15px;
    }

    .announcement-card {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .announcement-card-header {
        padding: 22px 26px;
        border-bottom: 1px solid #e8edf3;
        background: #f8fafc;
    }

    .announcement-card-header h3 {
        margin: 0;
        color: #071629;
        font-size: 20px;
        font-weight: 900;
    }

    .announcement-card-body {
        padding: 26px;
    }

    .announcement-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .announcement-stat {
        padding: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 17px;
        background: #ffffff;
    }

    .announcement-stat span {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .announcement-stat strong {
        color: #071629;
        font-size: 25px;
        font-weight: 900;
    }

    .announcement-filters {
        display: grid;
        grid-template-columns: 1fr 190px auto auto;
        gap: 12px;
        margin-bottom: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #172033;
        font-size: 14px;
        font-weight: 800;
    }

    .required {
        color: #dc2626;
    }

    .form-control,
    .announcement-filters input,
    .announcement-filters select {
        width: 100%;
        min-height: 48px;
        padding: 0 14px;
        border: 1px solid #d6dee8;
        border-radius: 11px;
        background: #ffffff;
        color: #071629;
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
    }

    textarea.form-control {
        min-height: 160px;
        padding-top: 14px;
        resize: vertical;
        line-height: 1.6;
    }

    .form-control:focus,
    .announcement-filters input:focus,
    .announcement-filters select:focus {
        border-color: #0b1b2b;
        box-shadow: 0 0 0 4px rgba(11, 27, 43, 0.08);
    }

    .help-text {
        display: block;
        margin-top: 7px;
        color: #8491a3;
        font-size: 12px;
    }

    .error-text {
        display: block;
        margin-top: 7px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 700;
    }

    .form-alert {
        margin-bottom: 20px;
        padding: 15px 17px;
        border-radius: 12px;
    }

    .form-alert.success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .form-alert.error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .form-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .btn {
        min-height: 44px;
        padding: 0 18px;
        border: 1px solid transparent;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
    }

    .btn-dark {
        border-color: #0b1b2b;
        background: #0b1b2b;
        color: #ffffff;
    }

    .btn-light {
        border-color: #dce3eb;
        background: #ffffff;
        color: #172033;
    }

    .btn-danger {
        border-color: #fecaca;
        background: #fff1f2;
        color: #dc2626;
    }

    .btn-blue {
        border-color: #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .btn-sm {
        min-height: 36px;
        padding: 0 12px;
        font-size: 12px;
    }

    .form-actions {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e8edf3;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .announcement-table-wrap {
        overflow-x: auto;
    }

    .announcement-table {
        width: 100%;
        border-collapse: collapse;
    }

    .announcement-table th,
    .announcement-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #e8edf3;
        text-align: left;
        vertical-align: middle;
    }

    .announcement-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .announcement-table td {
        color: #172033;
        font-size: 14px;
    }

    .announcement-title-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
    }

    .announcement-thumb {
        width: 64px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .announcement-title-cell strong {
        display: block;
        margin-bottom: 4px;
    }

    .announcement-title-cell small {
        color: #8491a3;
    }

    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        display: inline-flex;
        font-size: 12px;
        font-weight: 800;
    }

    .badge.active {
        background: #dcfce7;
        color: #15803d;
    }

    .badge.inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge.scheduled {
        background: #fef3c7;
        color: #a16207;
    }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .inline-form {
        display: inline;
        margin: 0;
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-top: 16px;
    }

    .image-preview-item {
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
    }

    .image-preview-item img {
        width: 100%;
        height: 150px;
        display: block;
        object-fit: cover;
    }

    .image-preview-footer {
        padding: 10px;
        display: flex;
        justify-content: center;
    }

    .empty-state {
        padding: 45px 20px;
        color: #8491a3;
        text-align: center;
    }

    @media (max-width: 900px) {
        .announcement-stats,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .announcement-filters {
            grid-template-columns: 1fr;
        }

        .image-preview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .announcement-header {
            flex-direction: column;
        }

        .image-preview-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>
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


    .main-special-spin-box {
    padding: 20px;
    border: 1px solid #ddd6fe;
    border-radius: 16px;
    background: #faf8ff;
}

.main-special-spin-heading {
    margin: 0 0 16px;
    color: #4c1d95;
    font-size: 16px;
    font-weight: 900;
}

.main-special-discount-list {
    margin-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.main-special-discount-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: end;
}

.main-special-discount-field {
    min-width: 0;
}

.main-special-input-wrap {
    position: relative;
}

.main-special-input-wrap input {
    width: 100%;
    padding-right: 48px;
    box-sizing: border-box;
}

.main-special-input-suffix {
    position: absolute;
    top: 50%;
    right: 16px;
    color: #64748b;
    font-weight: 800;
    transform: translateY(-50%);
    pointer-events: none;
}

.add-main-special-btn,
.remove-main-special-btn {
    min-height: 44px;
    padding: 0 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.add-main-special-btn {
    margin-top: 14px;
    border: 1px solid #6d28d9;
    background: #6d28d9;
    color: #ffffff;
}

.remove-main-special-btn {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

.add-main-special-btn:disabled,
.remove-main-special-btn:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.main-special-disabled {
    opacity: 0.65;
}

.main-awarded-rewards {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid #ddd6fe;
}

.main-awarded-rewards h4 {
    margin: 0 0 12px;
    color: #92400e;
    font-size: 14px;
    font-weight: 900;
}

.main-awarded-item {
    margin-top: 9px;
    padding: 11px 13px;
    border: 1px solid #fde68a;
    border-radius: 10px;
    background: #fffbeb;
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.main-awarded-item strong {
    color: #92400e;
}

.main-awarded-item span {
    color: #a16207;
    font-size: 12px;
}

@media (max-width: 700px) {
    .main-special-discount-row {
        grid-template-columns: 1fr;
    }

    .remove-main-special-btn {
        width: 100%;
    }

    .main-awarded-item {
        flex-direction: column;
    }
}
.main-special-checkbox {
    min-height: 48px;
    padding: 0 15px;
    border: 1px solid #d8e0e8;
    border-radius: 12px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    color: #172033;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
}

.main-special-checkbox input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px;
    flex: 0 0 18px;
    margin: 0;
    padding: 0;
}
</style>
@extends('layouts.app')

@section('content')
<style>
    .contacts-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #ffffff;
        min-height: 100vh;
    }

    /* ================= HEADER ================= */
    .contacts-header {
        background: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 50%, #2563eb 100%);
        padding: 24px 32px;
        border-radius: 20px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(13, 59, 102, 0.3);
    }

    .contacts-header .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .contacts-header .header-icon {
        width: 52px;
        height: 52px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .contacts-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .contacts-header .header-subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
    }

    .contacts-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-primary {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 10px 22px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(10px);
        border: none;
    }

    .btn-primary:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-primary svg {
        width: 18px;
        height: 18px;
    }

    /* ================= TOAST ================= */
    .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
        width: 100%;
    }

    .toast {
        background: white;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border-left: 5px solid;
        animation: slideInRight 0.4s ease;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .toast.hide {
        animation: slideOutRight 0.4s ease forwards;
    }

    .toast-success { border-left-color: #10b981; }
    .toast-error { border-left-color: #ef4444; }
    .toast-warning { border-left-color: #f59e0b; }
    .toast-info { border-left-color: #3b82f6; }

    .toast .toast-icon {
        font-size: 24px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .toast .toast-content {
        flex: 1;
    }

    .toast .toast-title {
        font-weight: 600;
        font-size: 14px;
        color: #0f172a;
    }

    .toast .toast-message {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
    }

    .toast .toast-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #94a3b8;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .toast .toast-close:hover {
        color: #475569;
    }

    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }

    /* ================= TABLE ================= */
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.6);
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: #fafbfc;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-header .header-right {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .table-header .table-info {
        font-size: 13px;
        color: #94a3b8;
    }

    .table-header .table-info strong {
        color: #0f172a;
    }

    /* PerPage Selector */
    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .perpage-selector select {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 13px;
        color: #0f172a;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .table-scroll {
        overflow-x: auto;
        padding: 0 24px 24px;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid #f1f5f9;
        background: #fafbfc;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-size: 14px;
        vertical-align: middle;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr:hover {
        background: #f8fafc;
    }

    /* Contact Info */
    .contact-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .contact-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .contact-avatar.color-1 { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .contact-avatar.color-2 { background: linear-gradient(135deg, #10b981, #34d399); }
    .contact-avatar.color-3 { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .contact-avatar.color-4 { background: linear-gradient(135deg, #ef4444, #f87171); }
    .contact-avatar.color-5 { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .contact-avatar.color-6 { background: linear-gradient(135deg, #25D366, #128C7E); }

    .contact-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 14px;
    }

    .contact-phone {
        font-size: 13px;
        color: #64748b;
        font-family: 'Courier New', monospace;
        background: #f8fafc;
        padding: 2px 10px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Actions */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-edit {
        background: #f59e0b;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-edit:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-delete {
        background: #ef4444;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .contact-no {
        font-weight: 700;
        color: #94a3b8;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        min-width: 30px;
        display: inline-block;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: #0f172a;
        font-size: 18px;
        margin: 0 0 8px;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 24px 20px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
        border-radius: 0 0 16px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 13px;
        color: #64748b;
    }

    .pagination-info strong {
        color: #0f172a;
    }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 6px 12px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
    }

    .pagination-links .page-link:hover:not(.active) {
        background: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    .pagination-links .page-link.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .pagination-links .page-link.disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-links .page-dots {
        padding: 6px 4px;
        color: #94a3b8;
    }

    /* ================= MODAL ================= */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafbfc;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-header h2 .modal-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #25D366, #128C7E);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #94a3b8;
        cursor: pointer;
        padding: 0 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .modal-body {
        padding: 24px;
        max-height: 55vh;
        overflow-y: auto;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #fafbfc;
        border-radius: 0 0 20px 20px;
    }

    /* ================= FORM DALAM MODAL ================= */
    .modal-body .form-group {
        margin-bottom: 18px;
    }

    .modal-body .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .modal-body .form-group label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .modal-body .form-group .helper-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .modal-body .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        color: #0f172a;
        transition: all 0.2s ease;
        background: #fafbfc;
        outline: none;
    }

    .modal-body .form-control:focus {
        border-color: #25D366;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        background: white;
    }

    .modal-body .form-control.error {
        border-color: #ef4444;
    }

    .modal-body select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .modal-body .error-message {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .modal-body .info-box {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .modal-body .info-box .info-icon {
        font-size: 18px;
        margin-top: 1px;
    }

    .modal-body .info-box .info-content {
        font-size: 13px;
        line-height: 1.5;
    }

    .modal-body .info-box .info-content strong {
        display: block;
        margin-bottom: 2px;
    }

    .btn-submit-modal {
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    }

    .btn-submit-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .btn-submit-modal.edit-mode {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-submit-modal.edit-mode:hover {
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .btn-cancel-modal {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 24px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cancel-modal:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ================= MODAL DELETE ================= */
    .modal-delete-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-delete-overlay.active {
        display: flex;
    }

    .modal-delete-content {
        background: white;
        border-radius: 20px;
        max-width: 420px;
        width: 90%;
        padding: 32px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-delete-content .delete-icon {
        font-size: 56px;
        margin-bottom: 12px;
    }

    .modal-delete-content h3 {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
    }

    .modal-delete-content p {
        margin: 0 0 4px 0;
        color: #64748b;
        font-size: 14px;
    }

    .modal-delete-content .delete-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 16px;
        margin: 8px 0 20px 0;
    }

    .modal-delete-content .delete-warning {
        color: #94a3b8;
        font-size: 13px;
        margin-bottom: 24px;
    }

    .modal-delete-content .delete-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .btn-delete-cancel {
        padding: 10px 24px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: white;
        color: #475569;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .btn-delete-cancel:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    .btn-delete-confirm {
        padding: 10px 24px;
        border: none;
        border-radius: 10px;
        background: #ef4444;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-delete-confirm:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {
        .contacts-container { padding: 16px; }
        .contacts-header {
            padding: 20px 24px;
            flex-direction: column;
            align-items: stretch;
            border-radius: 16px;
        }
        .contacts-header h1 { font-size: 20px; }
        .contacts-header .header-icon { width: 44px; height: 44px; font-size: 20px; }
        .table-scroll { padding: 0 12px 12px; }
        .table-container thead th,
        .table-container tbody td { padding: 10px 10px; font-size: 12px; }
        .contact-avatar { width: 32px; height: 32px; font-size: 12px; }
        .btn-edit, .btn-delete { font-size: 11px; padding: 4px 10px; }
        .modal-content { width: 95%; }
        .modal-footer { flex-direction: column; }
        .btn-submit-modal, .btn-cancel-modal { justify-content: center; }
        .toast-container { top: 16px; right: 16px; max-width: calc(100% - 32px); }
        .pagination-wrapper { flex-direction: column; align-items: stretch; }
        .pagination-links { justify-content: center; }
        .modal-delete-content { padding: 24px; }
        .modal-delete-content .delete-actions { flex-direction: column; }
        .perpage-selector { font-size: 12px; }
        .perpage-selector select { padding: 4px 8px; font-size: 12px; }
        .table-header { flex-direction: column; align-items: stretch; gap: 8px; }
    }

    @media (max-width: 480px) {
        .table-container thead th,
        .table-container tbody td { padding: 8px 6px; font-size: 11px; }
        .action-buttons { flex-direction: column; gap: 4px; }
        .btn-edit, .btn-delete { font-size: 10px; padding: 3px 8px; justify-content: center; }
        .contact-avatar { width: 28px; height: 28px; font-size: 11px; }
        .contact-name { font-size: 13px; }
        .contact-phone { font-size: 11px; }
        .contacts-header h1 { font-size: 17px; }
        .btn-primary { font-size: 12px; padding: 8px 16px; }
        .modal-header h2 { font-size: 15px; }
        .modal-body { padding: 14px; }
        .pagination-links .page-link { padding: 4px 8px; font-size: 11px; min-width: 30px; }
        .modal-delete-content { padding: 20px; }
        .modal-delete-content .delete-icon { font-size: 40px; }
        .modal-delete-content h3 { font-size: 17px; }
        .perpage-selector { font-size: 11px; }
        .perpage-selector select { padding: 3px 6px; font-size: 11px; }
    }
</style>

<div class="contacts-container">
    <!-- ================= TOAST CONTAINER ================= -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ================= HEADER ================= -->
    <div class="contacts-header">
        <div class="header-left">
            <div class="header-icon">💬</div>
            <div>
                <h1>Contacts WhatsApp</h1>
                <div class="header-subtitle">Manage your WhatsApp notification contacts</div>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openCreateModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Tambah Kontak
            </button>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="table-container">
        <div class="table-header">
            <h2>📋 Daftar Kontak WhatsApp</h2>
            <div class="header-right">
                <div class="perpage-selector">
                    <label for="perPage">Tampilkan:</label>
                    <select id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('perPage') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data</span>
                </div>
                <span class="table-info">
                    Total <strong>{{ $contacts->total() }}</strong> kontak
                </span>
            </div>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Nomor WhatsApp</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $index => $contact)
                        @php
                            $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6'];
                            $colorClass = $colors[$index % count($colors)];
                            $initials = strtoupper(substr($contact->name, 0, 2));
                            $no = ($contacts->currentPage() - 1) * $contacts->perPage() + $loop->iteration;
                        @endphp
                        <tr>
                            <td><span class="contact-no">{{ $no }}</span></td>
                            <td>
                                <div class="contact-info">
                                    <div class="contact-avatar {{ $colorClass }}">{{ $initials }}</div>
                                    <div>
                                        <div class="contact-name">{{ $contact->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="contact-phone">{{ $contact->phone }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="openEditModal({{ $contact->id }})" class="btn-edit">✏️ Edit</button>
                                    <button onclick="openDeleteModal({{ $contact->id }}, '{{ addslashes($contact->name) }}')" class="btn-delete">🗑️ Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <span class="empty-icon">📭</span>
                                    <h3>Belum Ada Kontak</h3>
                                    <p>Mulai dengan menambahkan kontak WhatsApp pertama Anda</p>
                                    <br>
                                    <button onclick="openCreateModal()" class="btn-primary" style="display: inline-flex; background: #25D366; border: none; color: white;">
                                        + Tambah Kontak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contacts->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $contacts->firstItem() ?? 0 }}</strong> - <strong>{{ $contacts->lastItem() ?? 0 }}</strong> dari <strong>{{ $contacts->total() }}</strong> data
            </div>
            <div class="pagination-links">
                {{-- Previous Page --}}
                @if($contacts->onFirstPage())
                    <span class="page-link disabled">‹</span>
                @else
                    <a href="{{ $contacts->previousPageUrl() }}" class="page-link">‹</a>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $start = max(1, $contacts->currentPage() - 2);
                    $end = min($contacts->lastPage(), $contacts->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $contacts->url(1) }}" class="page-link">1</a>
                    @if($start > 2)
                        <span class="page-dots">…</span>
                    @endif
                @endif

                @foreach(range($start, $end) as $page)
                    @if($page == $contacts->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $contacts->url($page) }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($end < $contacts->lastPage())
                    @if($end < $contacts->lastPage() - 1)
                        <span class="page-dots">…</span>
                    @endif
                    <a href="{{ $contacts->url($contacts->lastPage()) }}" class="page-link">{{ $contacts->lastPage() }}</a>
                @endif

                {{-- Next Page --}}
                @if($contacts->hasMorePages())
                    <a href="{{ $contacts->nextPageUrl() }}" class="page-link">›</a>
                @else
                    <span class="page-link disabled">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= MODAL CREATE / EDIT ================= -->
<div class="modal-overlay" id="contactModal" onclick="if(event.target === this) closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" id="modalIcon">💬</span>
                <span id="modalTitle">Tambah Kontak</span>
            </h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Info Box -->
            <div class="info-box">
                <span class="info-icon">ℹ️</span>
                <div class="info-content">
                    <strong>Format Nomor WhatsApp:</strong>
                    Gunakan format internasional tanpa tanda +, spasi, atau tanda hubung.<br>
                    Contoh: <code>6281234567890</code> (Indonesia) atau <code>60123456789</code> (Malaysia)
                </div>
            </div>

            <form id="contactForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="contact_id" id="contactId" value="">

                <!-- Nama -->
                <div class="form-group">
                    <label for="modal_name">
                        Nama Kontak
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="modal_name"
                        class="form-control"
                        placeholder="Contoh: Budi Santoso"
                        required
                    >
                    <div class="helper-text">Nama lengkap atau nama panggilan kontak</div>
                </div>

                <!-- Nomor WhatsApp -->
                <div class="form-group">
                    <label for="modal_phone">
                        Nomor WhatsApp
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="phone" 
                        id="modal_phone"
                        class="form-control"
                        placeholder="Contoh: 6281234567890"
                        required
                    >
                    <div class="helper-text">Masukkan nomor dengan format internasional (tanpa +, spasi, atau tanda hubung)</div>
                </div>

                <!-- Status Aktif (Hidden) -->
                <input type="hidden" name="is_active" value="1">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeModal()">✕ Batal</button>
            <button class="btn-submit-modal" id="btnSubmitModal" onclick="submitForm()">💾 Simpan Kontak</button>
        </div>
    </div>
</div>

<!-- ================= MODAL DELETE ================= -->
<div class="modal-delete-overlay" id="deleteModal" onclick="if(event.target === this) closeDeleteModal()">
    <div class="modal-delete-content">
        <div class="delete-icon">🗑️</div>
        <h3>Hapus Kontak</h3>
        <p>Apakah Anda yakin ingin menghapus kontak</p>
        <div class="delete-name" id="deleteContactName">"Nama Kontak"</div>
        <p class="delete-warning">Tindakan ini tidak dapat dibatalkan!</p>
        <div class="delete-actions">
            <button class="btn-delete-cancel" onclick="closeDeleteModal()">✕ Batal</button>
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete-confirm">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast('success', 'Berhasil!', '{{ session('success') }}');
        @endif
        @if(session('error'))
            showToast('error', 'Gagal!', '{{ session('error') }}');
        @endif
        @if(session('warning'))
            showToast('warning', 'Peringatan!', '{{ session('warning') }}');
        @endif
        @if(session('info'))
            showToast('info', 'Info', '{{ session('info') }}');
        @endif
    });

    // ================= CHANGE PER PAGE =================
    function changePerPage(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', value);
        url.searchParams.set('page', '1'); // Reset ke halaman pertama
        window.location.href = url.toString();
    }

    // ================= TOAST =================
    function showToast(type, title, message) {
        const container = document.getElementById('toastContainer');
        const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()">&times;</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }
        }, 5000);
    }

    // ================= OPEN CREATE MODAL =================
    function openCreateModal() {
        const modal = document.getElementById('contactModal');
        const title = document.getElementById('modalTitle');
        const icon = document.getElementById('modalIcon');
        const btnSubmit = document.getElementById('btnSubmitModal');
        const form = document.getElementById('contactForm');

        form.reset();
        document.getElementById('contactId').value = '';
        document.getElementById('formMethod').value = 'POST';
        form.action = '{{ route('contacts.store') }}';

        title.textContent = 'Tambah Kontak';
        icon.textContent = '💬';
        btnSubmit.textContent = '💾 Simpan Kontak';
        btnSubmit.className = 'btn-submit-modal';
        btnSubmit.disabled = false;

        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            document.getElementById('modal_name').focus();
        }, 100);
    }

    // ================= OPEN EDIT MODAL =================
    function openEditModal(id) {
        fetch(`/contacts/${id}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const contact = data.data;
                const modal = document.getElementById('contactModal');
                const title = document.getElementById('modalTitle');
                const icon = document.getElementById('modalIcon');
                const btnSubmit = document.getElementById('btnSubmitModal');
                const form = document.getElementById('contactForm');

                document.getElementById('modal_name').value = contact.name;
                document.getElementById('modal_phone').value = contact.phone;
                document.getElementById('contactId').value = contact.id;
                document.getElementById('formMethod').value = 'PUT';
                form.action = `/contacts/${contact.id}`;

                title.textContent = 'Edit Kontak';
                icon.textContent = '✏️';
                btnSubmit.textContent = '💾 Update Kontak';
                btnSubmit.className = 'btn-submit-modal edit-mode';
                btnSubmit.disabled = false;

                document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
                document.querySelectorAll('.error-message').forEach(el => el.remove());

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    document.getElementById('modal_name').focus();
                }, 100);
            } else {
                showToast('error', 'Gagal!', data.message || 'Gagal mengambil data kontak');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Gagal!', 'Gagal mengambil data kontak');
        });
    }

    // ================= CLOSE MODAL =================
    function closeModal() {
        const modal = document.getElementById('contactModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ================= OPEN DELETE MODAL =================
    function openDeleteModal(id, name) {
        const modal = document.getElementById('deleteModal');
        const nameDisplay = document.getElementById('deleteContactName');
        const form = document.getElementById('deleteForm');

        nameDisplay.textContent = `"${name}"`;
        form.action = `/contacts/${id}`;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // ================= CLOSE DELETE MODAL =================
    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ================= SUBMIT FORM =================
    function submitForm() {
        const form = document.getElementById('contactForm');
        const btnSubmit = document.getElementById('btnSubmitModal');
        const name = document.getElementById('modal_name');
        const phone = document.getElementById('modal_phone');

        let hasError = false;

        if (name.value.trim() === '') {
            showFieldError(name, 'Nama kontak wajib diisi');
            hasError = true;
        } else if (name.value.length < 3) {
            showFieldError(name, 'Nama minimal 3 karakter');
            hasError = true;
        } else {
            removeFieldError(name);
        }

        if (phone.value.trim() === '') {
            showFieldError(phone, 'Nomor WhatsApp wajib diisi');
            hasError = true;
        } else if (!/^[0-9]{10,15}$/.test(phone.value.trim())) {
            showFieldError(phone, 'Nomor hanya boleh angka (10-15 digit)');
            hasError = true;
        } else {
            removeFieldError(phone);
        }

        if (hasError) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = '⏳ Menyimpan...';
        form.submit();
    }

    // ================= FIELD ERROR =================
    function showFieldError(input, message) {
        input.classList.add('error');
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.marginTop = '4px';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.innerHTML = '⚠️ ' + message;
    }

    function removeFieldError(input) {
        input.classList.remove('error');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    }

    // ================= KEYBOARD SHORTCUTS =================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteModal();
        }
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const modal = document.getElementById('contactModal');
            if (modal.classList.contains('active')) {
                e.preventDefault();
                submitForm();
            }
        }
    });

    // ================= VALIDASI REAL-TIME =================
    document.getElementById('modal_name').addEventListener('input', function() {
        if (this.value.trim() !== '' && this.value.length >= 3) {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });

    document.getElementById('modal_phone').addEventListener('input', function() {
        // Hanya izinkan angka
        this.value = this.value.replace(/\D/g, '');
        
        if (/^[0-9]{10,15}$/.test(this.value.trim())) {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });
</script>
@endsection
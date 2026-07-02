@extends('layouts.app')

@section('content')
<style>
    .service-edit-container {
        padding: 24px;
        max-width: 800px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .service-edit-card {
        background: white;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #eef2f6;
    }

    .service-edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .service-edit-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .service-edit-header h1 .icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .btn-back {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-back:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .form-group label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-group .helper-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .form-control {
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

    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        background: white;
    }

    .form-control.error {
        border-color: #ef4444;
    }

    .form-control.error:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-control:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .error-message {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-submit {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 24px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    /* Alert Error */
    .alert-error {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert-error ul li {
        list-style: disc;
    }

    /* Alert Info */
    .alert-info {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-display {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-display.up {
        background: #ecfdf5;
        color: #065f46;
    }

    .status-display.warning {
        background: #fffbeb;
        color: #92400e;
    }

    .status-display.down {
        background: #fef2f2;
        color: #991b1b;
    }

    .status-display.unknown {
        background: #f1f5f9;
        color: #64748b;
    }

    .status-display::before {
        content: '';
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .status-display.up::before {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    .status-display.warning::before {
        background: #f59e0b;
        animation: pulse 1.5s infinite;
    }

    .status-display.down::before {
        background: #ef4444;
        animation: pulse 1s infinite;
    }

    .status-display.unknown::before {
        background: #94a3b8;
    }

    @keyframes pulse {
        0%, 100% { 
            opacity: 1;
            transform: scale(1);
        }
        50% { 
            opacity: 0.4;
            transform: scale(0.8);
        }
    }

    @media (max-width: 640px) {
        .service-edit-container {
            padding: 12px;
        }

        .service-edit-card {
            padding: 20px;
        }

        .service-edit-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .service-edit-header h1 {
            font-size: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit,
        .btn-cancel {
            justify-content: center;
        }
    }
</style>

<div class="service-edit-container">
    <div class="service-edit-card">
        <!-- Header -->
        <div class="service-edit-header">
            <h1>
                <span class="icon">✏️</span>
                Edit Service
            </h1>
            <a href="{{ route('services') }}" class="btn-back">
                ← Kembali
            </a>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert-error">
                <span>⚠️</span>
                <div>
                    <strong>Ada kesalahan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Info Status -->
        <div class="alert-info">
            <span>ℹ️</span>
            <div>
                <strong>Status Saat Ini:</strong>
                @if($service->last_status == 'UP')
                    <span class="status-display up">Operational</span>
                @elseif($service->last_status == 'WARNING')
                    <span class="status-display warning">Warning</span>
                @elseif($service->last_status == 'DOWN')
                    <span class="status-display down">Down</span>
                @else
                    <span class="status-display unknown">Unknown</span>
                @endif
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('services.update', $service->id) }}">
            @csrf
            @method('PUT')

            <!-- Nama Service -->
            <div class="form-group">
                <label for="name">
                    Nama Service
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    class="form-control @error('name') error @enderror"
                    placeholder="Contoh: Website Utama, API Gateway, dll"
                    value="{{ old('name', $service->name) }}"
                    required
                >
                @error('name')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Nama yang mudah diingat untuk service ini</div>
            </div>

            <!-- Target URL / IP -->
            <div class="form-group">
                <label for="target">
                    Target URL / IP
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="target" 
                    id="target"
                    class="form-control @error('target') error @enderror"
                    placeholder="Contoh: https://example.com atau 192.168.1.1"
                    value="{{ old('target', $service->target) }}"
                    required
                >
                @error('target')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">URL lengkap dengan protocol (http:// atau https://) atau alamat IP</div>
            </div>

            <!-- Type -->
            <div class="form-group">
                <label for="type">
                    Type
                    <span class="required">*</span>
                </label>
                <select name="type" id="type" class="form-control @error('type') error @enderror" required>
                    <option value="http" {{ old('type', $service->type) == 'http' ? 'selected' : '' }}>HTTP</option>
                    <option value="https" {{ old('type', $service->type) == 'https' ? 'selected' : '' }}>HTTPS</option>
                    <option value="ping" {{ old('type', $service->type) == 'ping' ? 'selected' : '' }}>PING</option>
                    <option value="port" {{ old('type', $service->type) == 'port' ? 'selected' : '' }}>PORT</option>
                </select>
                @error('type')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Jenis monitoring yang akan digunakan</div>
            </div>

            <!-- ID Service (Readonly) -->
            <div class="form-group">
                <label for="id">ID Service</label>
                <input 
                    type="text" 
                    id="id"
                    class="form-control"
                    value="#{{ $service->id }}"
                    disabled
                >
                <div class="helper-text">ID tidak dapat diubah</div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    💾 Update Service
                </button>
                <a href="{{ route('services') }}" class="btn-cancel">
                    ✕ Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- DEBUG: Tampilkan URL form --}}
<p style="background: #f0f0f0; padding: 10px; border-radius: 4px; font-size: 12px; margin-top: 20px; text-align: center; color: #64748b;">
    🔍 Form akan mengirim ke: <code>{{ route('services.update', $service->id) }}</code>
</p>

@endsection
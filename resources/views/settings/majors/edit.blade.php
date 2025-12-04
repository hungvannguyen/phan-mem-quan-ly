@extends('layouts.default')

@section('content')
    <main class="management-page">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">Chỉnh sửa ngành đào tạo</h1>
                    <p class="page-subtitle">Cập nhật thông tin ngành đào tạo</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Current Info Card -->
            <div class="current-info-card">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    Thông tin hiện tại
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Tên ngành:</span>
                        <span class="info-value">{{ $major->major_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mã ngành:</span>
                        <span class="info-value">
                            <span class="major-badge">{{ $major->major_code }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Số sinh viên:</span>
                        <span class="info-value">{{ number_format($major->students()->count()) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày tạo:</span>
                        <span class="info-value">{{ $major->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="{{ route('settings.majors.update', $major->major_id) }}" method="POST" class="custom-form">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <!-- Major Name -->
                        <div class="form-field full-width">
                            <label for="major_name" class="field-label required">Tên ngành đào tạo</label>
                            <input type="text" id="major_name" name="major_name"
                                class="field-input @error('major_name') is-invalid @enderror"
                                placeholder="VD: Công nghệ thông tin, Kế toán, Quản trị kinh doanh..."
                                value="{{ old('major_name', $major->major_name) }}" required>
                            @error('major_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Nhập tên đầy đủ của ngành đào tạo
                            </small>
                        </div>

                        <!-- Major Code -->
                        <div class="form-field">
                            <label for="major_code" class="field-label required">Mã ngành</label>
                            <input type="text" id="major_code" name="major_code"
                                class="field-input @error('major_code') is-invalid @enderror"
                                placeholder="VD: IT01, ACC01, BUS01..." value="{{ old('major_code', $major->major_code) }}"
                                maxlength="20" style="text-transform: uppercase;" required>
                            @error('major_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Mã định danh duy nhất (tối đa 20 ký tự)
                            </small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Cập nhật thông tin
                        </button>
                        <a href="{{ route('settings.index') }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>

            <!-- Delete Card -->
            @if ($major->students()->count() === 0)
                <div class="danger-card">
                    <h3>
                        <i class="fas fa-exclamation-triangle"></i>
                        Vùng nguy hiểm
                    </h3>
                    <p>Xóa ngành đào tạo này khỏi hệ thống. Hành động này không thể hoàn tác.</p>
                    <form action="{{ route('settings.majors.destroy', $major->major_id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngành đào tạo này không? Hành động này không thể hoàn tác!');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash"></i>
                            Xóa ngành đào tạo này
                        </button>
                    </form>
                </div>
            @else
                <div class="warning-card">
                    <h3>
                        <i class="fas fa-lock"></i>
                        Không thể xóa
                    </h3>
                    <p>Ngành đào tạo này đang có <strong>{{ number_format($major->students()->count()) }}</strong> sinh
                        viên. Bạn cần chuyển các sinh viên sang ngành khác hoặc xóa sinh viên trước khi có thể xóa ngành
                        này.</p>
                </div>
            @endif
        </div>
    </main>

    <style>
        .current-info-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .current-info-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
        }

        .major-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            font-family: 'Courier New', monospace;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .danger-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .danger-card h3 {
            color: #dc2626;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .danger-card p {
            color: #7f1d1d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .warning-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .warning-card h3 {
            color: #d97706;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .warning-card p {
            color: #78350f;
            line-height: 1.6;
            margin: 0;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(220, 38, 38, 0.3);
        }

        .field-label.required::after {
            content: " *";
            color: #ef4444;
        }
    </style>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);

        // Auto uppercase major code input
        document.getElementById('major_code').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endsection

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
                    <h1 class="page-title">Chỉnh sửa loại văn bằng/chứng chỉ</h1>
                    <p class="page-subtitle">Cập nhật thông tin loại văn bằng hoặc chứng chỉ</p>
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
                        <span class="info-label">Tên loại:</span>
                        <span class="info-value">{{ $type->type_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mã tiền tố:</span>
                        <span class="info-value">
                            <span class="prefix-badge">{{ $type->prefix }}</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Số lượng phôi:</span>
                        <span class="info-value">{{ number_format($type->diplomaBlanks()->count()) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ngày tạo:</span>
                        <span class="info-value">{{ $type->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="{{ route('settings.types.update', $type->type_id) }}" method="POST" class="custom-form">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <!-- Type Name -->
                        <div class="form-field full-width">
                            <label for="type_name" class="field-label required">Tên loại văn bằng/chứng chỉ</label>
                            <input type="text" id="type_name" name="type_name"
                                class="field-input @error('type_name') is-invalid @enderror"
                                placeholder="VD: Bằng cử nhân, Chứng chỉ nghiệp vụ 6 tháng..."
                                value="{{ old('type_name', $type->type_name) }}" required>
                            @error('type_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Nhập tên đầy đủ của loại văn bằng hoặc chứng chỉ
                            </small>
                        </div>

                        <!-- Prefix -->
                        <div class="form-field">
                            <label for="prefix" class="field-label required">Mã tiền tố</label>
                            <input type="text" id="prefix" name="prefix"
                                class="field-input @error('prefix') is-invalid @enderror"
                                placeholder="VD: CN, KS, CCNV6T..." value="{{ old('prefix', $type->prefix) }}"
                                maxlength="20" style="text-transform: uppercase;" required>
                            @error('prefix')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Mã viết tắt dùng để phân loại (tối đa 20 ký tự)
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
            @if ($type->diplomaBlanks()->count() === 0)
                <div class="danger-card">
                    <h3>
                        <i class="fas fa-exclamation-triangle"></i>
                        Vùng nguy hiểm
                    </h3>
                    <p>Xóa loại văn bằng/chứng chỉ này khỏi hệ thống. Hành động này không thể hoàn tác.</p>
                    <form action="{{ route('settings.types.destroy', $type->type_id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại văn bằng này không? Hành động này không thể hoàn tác!');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash"></i>
                            Xóa loại văn bằng này
                        </button>
                    </form>
                </div>
            @else
                <div class="warning-card">
                    <h3>
                        <i class="fas fa-lock"></i>
                        Không thể xóa
                    </h3>
                    <p>Loại văn bằng/chứng chỉ này đang được sử dụng bởi
                        <strong>{{ number_format($type->diplomaBlanks()->count()) }}</strong> phôi văn bằng. Bạn cần xóa
                        hoặc chuyển đổi các phôi này trước khi có thể xóa loại văn bằng.</p>
                </div>
            @endif
        </div>
    </main>

    <style>
        .current-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .prefix-badge {
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

        // Auto uppercase prefix input
        document.getElementById('prefix').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endsection

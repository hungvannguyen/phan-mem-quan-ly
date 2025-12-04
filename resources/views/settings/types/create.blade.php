@extends('layouts.default')

@section('content')
    <main class="management-page">
        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">Thêm loại văn bằng/chứng chỉ mới</h1>
                    <p class="page-subtitle">Tạo mới loại văn bằng hoặc chứng chỉ trong hệ thống</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="{{ route('settings.types.store') }}" method="POST" class="custom-form">
                    @csrf

                    <div class="form-grid">
                        <!-- Type Name -->
                        <div class="form-field full-width">
                            <label for="type_name" class="field-label required">Tên loại văn bằng/chứng chỉ</label>
                            <input type="text" id="type_name" name="type_name"
                                class="field-input @error('type_name') is-invalid @enderror"
                                placeholder="VD: Bằng cử nhân, Chứng chỉ nghiệp vụ 6 tháng..."
                                value="{{ old('type_name') }}" required>
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
                                placeholder="VD: CN, KS, CCNV6T..." value="{{ old('prefix') }}" maxlength="20"
                                style="text-transform: uppercase;" required>
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
                            Lưu thông tin
                        </button>
                        <a href="{{ route('settings.index') }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>

            <!-- Information Card -->
            <div class="info-card">
                <h3>
                    <i class="fas fa-lightbulb"></i>
                    Hướng dẫn
                </h3>
                <ul>
                    <li>
                        <strong>Tên loại văn bằng/chứng chỉ:</strong> Nhập tên đầy đủ, rõ ràng.
                        <br>Ví dụ: "Bằng cử nhân", "Bằng thạc sĩ", "Chứng chỉ nghiệp vụ 6 tháng"
                    </li>
                    <li>
                        <strong>Mã tiền tố:</strong> Mã viết tắt để phân biệt các loại văn bằng/chứng chỉ.
                        <br>Ví dụ: "CN" cho Cử nhân, "THS" cho Thạc sĩ, "CCNV6T" cho Chứng chỉ nghiệp vụ 6 tháng
                    </li>
                    <li>Mã tiền tố sẽ được sử dụng trong việc tạo số seri phôi văn bằng</li>
                    <li>Cả tên và mã tiền tố đều phải là duy nhất trong hệ thống</li>
                </ul>
            </div>
        </div>
    </main>

    <style>
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .info-card h3 {
            color: #1e40af;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-card li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0f2fe;
            color: #1e3a8a;
            line-height: 1.6;
        }

        .info-card li:last-child {
            border-bottom: none;
        }

        .info-card strong {
            color: #1e40af;
        }

        .field-label.required::after {
            content: " *";
            color: #ef4444;
        }
    </style>

    <script>
        // Auto uppercase prefix input
        document.getElementById('prefix').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endsection

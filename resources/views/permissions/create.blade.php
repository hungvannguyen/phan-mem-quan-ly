@extends('layouts.default')

@section('content')
    <style>
        .field-description {
            margin-top: 0.5rem;
        }

        .alert-info {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }

        .alert-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .help-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .help-card h6 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .help-card ul {
            margin-bottom: 1rem;
        }

        .help-card code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.875rem;
        }
    </style>

    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Thêm Permission Mới</h1>
                    <p>Tạo quyền truy cập mới cho hệ thống</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('permissions.store') }}">
                    @csrf

                    <!-- Permission Information Section -->
                    <div class="section-card">
                        <h2 class="section-title">
                            <i class="fas fa-shield-alt text-blue-600"></i>
                            Thông tin Permission
                        </h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="form-field">
                                <label for="name" class="field-label">Tên Permission <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" class="field-input"
                                    value="{{ old('name') }}" placeholder="VD: diplomas.create" required>
                                <div class="field-description">
                                    <small class="text-gray-600">Format: module.action (VD: diplomas.create,
                                        users.view)</small>
                                </div>
                                @error('name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="display_name" class="field-label">Tên Hiển Thị <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="display_name" name="display_name" class="field-input"
                                    value="{{ old('display_name') }}" placeholder="VD: Cấp văn bằng mới" required>
                                @error('display_name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="category" class="field-label">Danh Mục <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="category" name="category" class="field-input"
                                    value="{{ old('category') }}" list="categories"
                                    placeholder="VD: diplomas, users, settings" required>
                                <datalist id="categories">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                                <div class="field-description">
                                    <small class="text-gray-600">Chọn từ danh sách có sẵn hoặc nhập danh mục mới</small>
                                </div>
                                @error('category')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="description" class="field-label">Mô Tả</label>
                                <textarea id="description" name="description" class="field-input" rows="3"
                                    placeholder="Mô tả chi tiết về permission này...">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-section">
                        <div class="last-updated">
                            Tạo permission mới
                        </div>
                        <div class="action-buttons">
                            <button type="button" onclick="history.back()" class="btn-cancel">
                                <i class="fas fa-times mr-2"></i>Hủy
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save mr-2"></i>Lưu Permission
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Help Sidebar -->
            <div class="lg:col-span-1">
                <div class="help-card">
                    <h6>
                        <i class="fas fa-info-circle text-blue-600"></i>
                        Quy tắc đặt tên
                    </h6>
                    <ul class="text-sm text-gray-700">
                        <li class="mb-2"><strong>Tên Permission:</strong> Sử dụng format <code>module.action</code></li>
                        <li class="mb-2"><strong>Module:</strong> diplomas, certificates, users, settings...</li>
                        <li class="mb-2"><strong>Action:</strong> view, create, edit, delete, export...</li>
                    </ul>

                    <h6 class="mt-4">
                        <i class="fas fa-lightbulb text-yellow-600"></i>
                        Ví dụ
                    </h6>
                    <ul class="text-sm text-gray-700">
                        <li class="mb-2"><code>diplomas.view</code> - Xem danh sách văn bằng</li>
                        <li class="mb-2"><code>diplomas.create</code> - Cấp văn bằng mới</li>
                        <li class="mb-2"><code>users.delete</code> - Xóa người dùng</li>
                        <li class="mb-2"><code>settings.edit</code> - Chỉnh sửa cài đặt</li>
                    </ul>

                    <div class="alert-warning mb-0 mt-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Lưu ý:</strong> Tên permission phải là duy nhất trong hệ thống.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="flash-message success" id="success-message">
                <div class="flash-content">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('success-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message error" id="error-message">
                <div class="flash-content">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('error-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            if (successMsg) successMsg.remove();
            if (errorMsg) errorMsg.remove();
        }, 5000);

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required]');

            form.addEventListener('submit', function(e) {
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');

                        field.addEventListener('input', function() {
                            this.classList.remove('border-red-500');
                        });
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ các trường bắt buộc!');
                }
            });
        });
    </script>
@endsection

<style>
    .field-description {
        margin-top: 0.5rem;
    }

    .alert-info {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-top: 1rem;
        background-color: #dbeafe;
        border: 1px solid #3b82f6;
        color: #1e40af;
    }

    .alert-warning {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-top: 1rem;
        background-color: #fef3c7;
        border: 1px solid #f59e0b;
        color: #92400e;
    }

    .help-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .help-card h6 {
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .help-card ul {
        margin-bottom: 1rem;
    }

    .help-card code {
        background: #e2e8f0;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.875rem;
    }
</style>

<div class="student-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex w-full items-center justify-between">
            <div>
                <h1>Thêm Permission Mới</h1>
                <p>Tạo quyền truy cập mới cho hệ thống</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="history.back()" class="btn btn-secondary">
                    Quay lại
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('permissions.store') }}">
                @csrf

                <!-- Permission Information Section -->
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="fas fa-shield-alt text-blue-600"></i>
                        Thông tin Permission
                    </h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="form-field">
                            <label for="name" class="field-label">Tên Permission <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" class="field-input"
                                value="{{ old('name') }}" placeholder="VD: diplomas.create" required>
                            <div class="field-description">
                                <small class="text-gray-600">Format: module.action (VD: diplomas.create,
                                    users.view)</small>
                            </div>
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="display_name" class="field-label">Tên Hiển Thị <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="display_name" name="display_name" class="field-input"
                                value="{{ old('display_name') }}" placeholder="VD: Cấp văn bằng mới" required>
                            @error('display_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="category" class="field-label">Danh Mục <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="category" name="category" class="field-input"
                                value="{{ old('category') }}" list="categories"
                                placeholder="VD: diplomas, users, settings" required>
                            <datalist id="categories">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            <div class="field-description">
                                <small class="text-gray-600">Chọn từ danh sách có sẵn hoặc nhập danh mục mới</small>
                            </div>
                            @error('category')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="description" class="field-label">Mô Tả</label>
                            <textarea id="description" name="description" class="field-input" rows="3"
                                placeholder="Mô tả chi tiết về permission này...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section">
                    <div class="last-updated">
                        Tạo permission mới
                    </div>
                    <div class="action-buttons">
                        <button type="button" onclick="history.back()" class="btn-cancel">
                            <i class="fas fa-times mr-2"></i>Hủy
                        </button>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save mr-2"></i>Lưu Permission
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Help Sidebar -->
        <div class="lg:col-span-1">
            <div class="help-card">
                <h6>
                    <i class="fas fa-info-circle text-blue-600"></i>
                    Quy tắc đặt tên
                </h6>
                <ul class="text-sm text-gray-700">
                    <li class="mb-2"><strong>Tên Permission:</strong> Sử dụng format <code>module.action</code></li>
                    <li class="mb-2"><strong>Module:</strong> diplomas, certificates, users, settings...</li>
                    <li class="mb-2"><strong>Action:</strong> view, create, edit, delete, export...</li>
                </ul>

                <h6 class="mt-4">
                    <i class="fas fa-lightbulb text-yellow-600"></i>
                    Ví dụ
                </h6>
                <ul class="text-sm text-gray-700">
                    <li class="mb-2"><code>diplomas.view</code> - Xem danh sách văn bằng</li>
                    <li class="mb-2"><code>diplomas.create</code> - Cấp văn bằng mới</li>
                    <li class="mb-2"><code>users.delete</code> - Xóa người dùng</li>
                    <li class="mb-2"><code>settings.edit</code> - Chỉnh sửa cài đặt</li>
                </ul>

                <div class="alert-warning mb-0 mt-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Lưu ý:</strong> Tên permission phải là duy nhất trong hệ thống.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="flash-message success" id="success-message">
            <div class="flash-content">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button onclick="document.getElementById('success-message').remove()" class="flash-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flash-message error" id="error-message">
            <div class="flash-content">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button onclick="document.getElementById('error-message').remove()" class="flash-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-hide success/error messages after 5 seconds
    setTimeout(() => {
        const successMsg = document.getElementById('success-message');
        const errorMsg = document.getElementById('error-message');
        if (successMsg) successMsg.remove();
        if (errorMsg) errorMsg.remove();
    }, 5000);

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const requiredFields = form.querySelectorAll('input[required]');

        form.addEventListener('submit', function(e) {
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');

                    field.addEventListener('input', function() {
                        this.classList.remove('border-red-500');
                    });
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ các trường bắt buộc!');
            }
        });
    });
</script>
@endsection
<div class="container-fluid px-4">
<div class="mb-4">
    <h1 class="mt-4">
        <i class="fas fa-plus-circle"></i> Thêm Permission Mới
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-file-alt"></i> Thông Tin Permission
            </div>
            <div class="card-body">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Tên Permission <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name" name="name" value="{{ old('name') }}"
                            placeholder="VD: diplomas.create" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Format: module.action (VD: diplomas.create, users.view)
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="display_name" class="form-label">
                            Tên Hiển Thị <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                            id="display_name" name="display_name" value="{{ old('display_name') }}"
                            placeholder="VD: Cấp văn bằng mới" required>
                        @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">
                            Danh Mục <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror"
                            id="category" name="category" value="{{ old('category') }}" list="categories"
                            placeholder="VD: diplomas, users, settings" required>
                        <datalist id="categories">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô Tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                            rows="3" placeholder="Mô tả chi tiết về permission này...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu Permission
                        </button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Hướng Dẫn
            </div>
            <div class="card-body">
                <h6>Quy tắc đặt tên:</h6>
                <ul class="small">
                    <li><strong>Tên Permission:</strong> Sử dụng format <code>module.action</code></li>
                    <li><strong>Module:</strong> diplomas, certificates, users, settings...</li>
                    <li><strong>Action:</strong> view, create, edit, delete, export...</li>
                </ul>

                <h6 class="mt-3">Ví dụ:</h6>
                <ul class="small">
                    <li><code>diplomas.view</code> - Xem danh sách văn bằng</li>
                    <li><code>diplomas.create</code> - Cấp văn bằng mới</li>
                    <li><code>users.delete</code> - Xóa người dùng</li>
                </ul>

                <div class="alert alert-warning small mb-0 mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Lưu ý:</strong> Tên permission phải là duy nhất trong hệ thống.
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

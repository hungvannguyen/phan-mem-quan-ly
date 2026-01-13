@extends('layouts.default')

@section('content')
    <main class="management-page">
        <div class="form-section">
            <!-- Header -->
            <div class="page">
                <h1 class="page-title">
                    <i class="fas fa-file-import text-blue-600"></i>
                    Import Dữ liệu
                </h1>
                <p class="page-subtitle">
                    Upload file Excel để import dữ liệu vào hệ thống. Hệ thống hỗ trợ import nhiều loại dữ liệu khác nhau.
                </p>
            </div>

            <!-- Alert Messages -->
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-times-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <ul class="list-disc ml-6 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Import Form -->
                <div class="lg:col-span-2">
                    <div class="search-card">
                        <h3 class="section-subtitle mb-4">
                            <i class="fas fa-upload"></i> Upload File
                        </h3>

                        <form action="{{ route('import.handle') }}" method="POST" enctype="multipart/form-data"
                            id="importForm">
                            @csrf
                            <input type="hidden" name="use_queue" value="1">

                            <!-- Import Type Selection -->
                            <div class="form-group">
                                <label for="import_type">
                                    Loại dữ liệu <span class="text-red-500">*</span>
                                </label>
                                <select name="import_type" id="import_type" required>
                                    <option value="">-- Chọn loại dữ liệu --</option>
                                    <option value="degree">Bằng Cử nhân, Thạc sĩ, Tiến sĩ</option>
                                    <option value="political_theory">Lý luận chính trị</option>
                                    <option value="certificate">Chứng chỉ</option>
                                </select>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    Chọn loại dữ liệu phù hợp với file Excel của bạn
                                </p>
                            </div>

                            <!-- File Upload -->
                            <div class="form-group">
                                <label for="excel_file">
                                    File Excel <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    Chấp nhận: .xlsx, .xls, .csv (Tối đa 10MB)
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="action-container">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i>
                                    Bắt đầu Import
                                </button>
                                <a href="{{ route('import.logs') }}" class="btn btn-secondary">
                                    <i class="fas fa-history"></i>
                                    Xem Logs
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column - Templates & Instructions -->
                <div class="space-y-6">
                    <!-- Download Templates -->
                    <div class="search-card">
                        <h3 class="section-subtitle mb-4">
                            <i class="fas fa-download text-green-600"></i> Templates
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Tải về file mẫu để biết cấu trúc đúng
                        </p>
                        <div class="space-y-2">
                            @forelse($templates ?? [] as $index => $template)
                                <a href="{{ route('import.download-template', $index) }}"
                                    class="block text-blue-600 hover:text-blue-800 hover:underline">
                                    <i class="fas fa-file-excel text-green-600"></i>
                                    {{ $template }}
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-info-circle"></i>
                                    Chưa có template nào
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="search-card">
                        <h3 class="section-subtitle mb-4">
                            <i class="fas fa-info-circle text-blue-600"></i> Hướng dẫn
                        </h3>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700">
                            <li>Chọn loại dữ liệu muốn import</li>
                            <li>Tải template tương ứng</li>
                            <li>Điền dữ liệu vào template</li>
                            <li>Upload file và bắt đầu import</li>
                            <li>Kiểm tra logs để xem kết quả</li>
                        </ol>

                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Lưu ý:</strong> Đảm bảo file Excel đúng cấu trúc. File không đúng sẽ gây lỗi
                                import.
                            </p>
                        </div>
                    </div>

                    <!-- Recent Stats -->
                    <div class="search-card">
                        <h3 class="section-subtitle mb-4">
                            <i class="fas fa-chart-line text-purple-600"></i> Thống kê nhanh
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Tổng import hôm nay</span>
                                <span class="font-semibold text-blue-600">--</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Thành công</span>
                                <span class="font-semibold text-green-600">--</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Có lỗi</span>
                                <span class="font-semibold text-red-600">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('importForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
        });
    </script>
@endsection

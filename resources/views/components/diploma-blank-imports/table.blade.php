{{-- Table component cho DiplomaBlankImport --}}
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Danh sách Nhập Phôi Văn Bằng</h3>
        <div class="table-stats">
            <span class="stat-item">
                <strong>{{ $diplomaBlankImports->total() ?? 0 }}</strong> bản ghi
            </span>
        </div>
    </div>

    @if ($diplomaBlankImports && $diplomaBlankImports->count() > 0)
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="th">ID</th>
                        <th class="th">Số văn bản</th>
                        <th class="th">Loại phôi</th>
                        <th class="th">Ngày ban hành</th>
                        <th class="th">Ngày nhập</th>
                        <th class="th">Dải số</th>
                        <th class="th">Số lượng</th>
                        <th class="th">Tiến độ</th>
                        <th class="th">Trạng thái</th>
                        <th class="th">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($diplomaBlankImports as $import)
                        <tr class="table-row">
                            <td class="td">{{ $import->id }}</td>
                            <td class="td-document">
                                <span class="document-ref">{{ $import->document_reference }}</span>
                            </td>
                            <td class="td">
                                <span class="type-name">{{ $import->diplomaBlankType->type_name ?? 'N/A' }}</span>
                            </td>
                            <td class="td">
                                <span class="date-text">{{ $import->issue_date?->format('d/m/Y') ?? 'N/A' }}</span>
                            </td>
                            <td class="td">
                                <span class="date-text">{{ $import->import_date?->format('d/m/Y') ?? 'N/A' }}</span>
                            </td>
                            <td class="td-range">
                                <div class="serial-range">
                                    <span class="range-text">
                                        {{ $import->prefix ?? '' }}{{ $import->from_number }}{{ $import->suffix ?? '' }}
                                        -
                                        {{ $import->prefix ?? '' }}{{ $import->to_number }}{{ $import->suffix ?? '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="td">
                                <span class="quantity-text">{{ number_format($import->total_quantity) }}</span>
                            </td>
                            <td class="td">
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        @php
                                            $percentage =
                                                $import->total_quantity > 0
                                                    ? ($import->processed_count / $import->total_quantity) * 100
                                                    : 0;
                                        @endphp
                                        <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span
                                        class="progress-text">{{ $import->processed_count }}/{{ $import->total_quantity }}</span>
                                </div>
                            </td>
                            <td class="td">
                                @php
                                    $statusClass = match ($import->status) {
                                        App\Enums\ImportStatus::PENDING => 'status-pending',
                                        App\Enums\ImportStatus::PROCESSING => 'status-processing',
                                        App\Enums\ImportStatus::COMPLETED => 'status-completed',
                                        App\Enums\ImportStatus::FAILED => 'status-failed',
                                        default => 'status-unknown',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $import->status->getLabel() }}
                                </span>
                            </td>
                            <td class="td">
                                <div class="action-buttons">
                                    <button type="button" class="btn-action btn-view" title="Xem chi tiết"
                                        data-import-id="{{ $import->id }}"
                                        onclick="viewImport({{ $import->id }})">
                                        Xem
                                    </button>

                                    @if ($import->status === App\Enums\ImportStatus::PENDING)
                                        <button type="button" class="btn-action btn-start" title="Bắt đầu xử lý"
                                            data-import-id="{{ $import->id }}"
                                            onclick="startImport({{ $import->id }})">
                                            Bắt đầu
                                        </button>
                                    @endif

                                    @if ($import->status === App\Enums\ImportStatus::PROCESSING)
                                        <button type="button" class="btn-action btn-pause" title="Tạm dừng"
                                            data-import-id="{{ $import->id }}"
                                            onclick="pauseImport({{ $import->id }})">
                                            Tạm dừng
                                        </button>
                                    @endif

                                    @if ($import->status === App\Enums\ImportStatus::FAILED)
                                        <button type="button" class="btn-action btn-retry" title="Thử lại"
                                            data-import-id="{{ $import->id }}"
                                            onclick="retryImport({{ $import->id }})">
                                            Thử lại
                                        </button>
                                    @endif

                                    <button type="button" class="btn-action btn-delete" title="Xóa"
                                        data-import-id="{{ $import->id }}"
                                        onclick="deleteImport({{ $import->id }})">
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Custom Pagination Section --}}
        @if ($diplomaBlankImports->hasPages())
            <div class="table-pagination-wrapper">
                <x-pagination.custom :paginator="$diplomaBlankImports" item-name="lượt nhập phôi"
                    label="Diploma Blank Imports Pagination Navigation" :per-page-options="[5, 10, 15, 25, 50]" />
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="empty-title">Không có dữ liệu</h3>
            <p class="empty-message">
                @if (request()->hasAny([
                        'document_reference',
                        'type_id',
                        'status',
                        'import_date_from',
                        'import_date_to',
                        'issue_date_from',
                    ]))
                    Không tìm thấy bản ghi nào phù hợp với bộ lọc hiện tại.
                @else
                    Chưa có bản ghi nhập phôi văn bằng nào trong hệ thống.
                @endif
            </p>
            <div class="empty-actions">
                @if (request()->hasAny([
                        'document_reference',
                        'type_id',
                        'status',
                        'import_date_from',
                        'import_date_to',
                        'issue_date_from',
                    ]))
                    <a href="{{ route('diploma-blank-management') }}" class="btn-secondary">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                @endif
                <a href="{{ route('diploma-blank-import.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm mới
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    function viewImport(importId) {
        window.location.href = `/diploma-blank-management/${importId}`;
    }

    function startImport(importId) {
        if (confirm('Bạn có chắc chắn muốn bắt đầu xử lý import này?')) {
            fetch(`/diploma-blank-management/${importId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message);
                        location.reload();
                    } else {
                        showToast('error', data.message);
                    }
                })
                .catch(error => {
                    showToast('error', 'Có lỗi xảy ra khi xử lý yêu cầu.');
                });
        }
    }

    function pauseImport(importId) {
        if (confirm('Bạn có chắc chắn muốn tạm dừng xử lý import này?')) {
            fetch(`/diploma-blank-management/${importId}/pause`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message);
                        location.reload();
                    } else {
                        showToast('error', data.message);
                    }
                })
                .catch(error => {
                    showToast('error', 'Có lỗi xảy ra khi xử lý yêu cầu.');
                });
        }
    }

    function retryImport(importId) {
        if (confirm('Bạn có chắc chắn muốn thử lại import này?')) {
            fetch(`/diploma-blank-management/${importId}/retry`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message);
                        location.reload();
                    } else {
                        showToast('error', data.message);
                    }
                })
                .catch(error => {
                    showToast('error', 'Có lỗi xảy ra khi xử lý yêu cầu.');
                });
        }
    }

    function deleteImport(importId) {
        if (confirm('Bạn có chắc chắn muốn xóa import này?\n\nHành động này không thể hoàn tác.')) {
            fetch(`/diploma-blank-management/${importId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message);
                        location.reload();
                    } else {
                        showToast('error', data.message);
                    }
                })
                .catch(error => {
                    showToast('error', 'Có lỗi xảy ra khi xử lý yêu cầu.');
                });
        }
    }

    function showToast(type, message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className =
            `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }
</script>

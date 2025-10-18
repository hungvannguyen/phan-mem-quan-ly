{{-- Table component cho DiplomaBlank --}}
@props(['diplomaBlanks', 'importId' => null])

<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Danh sách Phôi Văn Bằng</h3>
        <div class="table-stats">
            <span class="stat-item">
                <strong>{{ $diplomaBlanks->total() ?? 0 }}</strong> bản ghi
            </span>
        </div>
    </div>

    @if ($diplomaBlanks && $diplomaBlanks->count() > 0)
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="th">Số seri</th>
                        <th class="th">Loại phôi</th>
                        <th class="th">Trạng thái</th>
                        <th class="th">Ngày nhập</th>
                        <th class="th">Ngày cấp</th>
                        <th class="th">Ngày thu hồi</th>
                        <th class="th">Lý do cấp</th>
                        <th class="th">Lý do thu hồi</th>
                        <th class="th">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="loading" class="loading-overlay hidden">
                        <td colspan="9" class="loading-cell">
                            <div class="spinner"></div>
                            <span class="loading-text">Đang tải dữ liệu...</span>
                        </td>
                    </tr>
                    @foreach ($diplomaBlanks as $blank)
                        <tr class="table-row" data-blank-id="{{ $blank->diploma_blank_id }}"
                            onclick="toggleRowHighlight(this)">
                            <td class="td">
                                <span class="serial-number">{{ $blank->serial_number }}</span>
                            </td>
                            <td class="td">
                                @if ($blank->type)
                                    <div class="type-info">
                                        <span class="type-name">{{ $blank->type->type_name }}</span>
                                        <small
                                            class="type-description text-muted d-block">{{ $blank->type->description ?? '' }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Chưa xác định</span>
                                @endif
                            </td>
                            <td class="td">
                                @php
                                    $status =
                                        $blank->status instanceof \App\Enums\DiplomaBlankStatus
                                            ? $blank->status
                                            : \App\Enums\DiplomaBlankStatus::tryFrom($blank->status);
                                    $statusClass = $status ? $status->getBadgeClass() : 'status-unknown';
                                    $statusText = $status ? $status->getLabel() : 'Không xác định';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="td">
                                @if ($blank->import_date)
                                    <span class="date-text">{{ $blank->import_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($blank->issue_date)
                                    <span class="date-text">{{ $blank->issue_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="td">
                                @if ($blank->recall_date)
                                    <span class="date-text">{{ $blank->recall_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="td">
                                <div class="issue-reason">
                                    @if ($blank->issue_reason)
                                        <span class="reason-text">{{ Str::limit($blank->issue_reason, 30) }}</span>
                                        @if (strlen($blank->issue_reason) > 30)
                                            <i class="fas fa-info-circle text-info ml-1"
                                                title="{{ $blank->issue_reason }}"></i>
                                        @endif
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </div>
                            </td>
                            <td class="td">
                                <div class="recall-reason">
                                    @if ($blank->recall_reason)
                                        <span class="reason-text">{{ Str::limit($blank->recall_reason, 30) }}</span>
                                        @if (strlen($blank->recall_reason) > 30)
                                            <i class="fas fa-info-circle text-info ml-1"
                                                title="{{ $blank->recall_reason }}"></i>
                                        @endif
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </div>
                            </td>
                            <td class="td">
                                <div class="action-buttons">
                                    <button type="button" class="btn-action btn-view" title="Xem chi tiết">
                                        Xem
                                    </button>

                                    @php
                                        $currentStatus =
                                            $blank->status instanceof \App\Enums\DiplomaBlankStatus
                                                ? $blank->status
                                                : \App\Enums\DiplomaBlankStatus::tryFrom($blank->status);
                                    @endphp

                                    @if ($currentStatus && $currentStatus->canIssue())
                                        <button type="button" class="btn-action btn-start" title="Cấp phôi">
                                            Cấp phôi
                                        </button>
                                    @elseif ($currentStatus && $currentStatus->canRecall())
                                        <button type="button" class="btn-action btn-pause" title="Thu hồi phôi">
                                            Thu hồi
                                        </button>
                                    @endif

                                    @if ($currentStatus && $currentStatus->canMarkAsDamaged())
                                        <button type="button" class="btn-action btn-delete" title="Báo hư hỏng">
                                            Báo hỏng
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Custom Pagination Section - Luôn hiển thị khi có pagination --}}
    @if (isset($diplomaBlanks) && $diplomaBlanks->hasPages())
        <div class="table-pagination-wrapper">
            <x-pagination.custom :paginator="$diplomaBlanks" item-name="phôi văn bằng"
                label="Diploma Blanks Pagination Navigation" :per-page-options="[5, 10, 15, 25, 50]" />
        </div>
    @elseif (isset($diplomaBlanks) && $diplomaBlanks->total() > 0)
        {{-- Hiển thị info khi không có nhiều trang nhưng có dữ liệu --}}
        <div class="table-pagination-wrapper">
            <div class="pagination-info">
                <span class="pagination-text">
                    Hiển thị {{ $diplomaBlanks->firstItem() ?? 0 }} đến {{ $diplomaBlanks->lastItem() ?? 0 }}
                    trong tổng số {{ $diplomaBlanks->total() }} phôi văn bằng
                </span>
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if (!isset($diplomaBlanks) || $diplomaBlanks->count() === 0)
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-file-contract"></i>
            </div>
            <h3 class="empty-title">Không có dữ liệu</h3>
            <p class="empty-message">
                @if (request()->hasAny(['serial_number', 'type_id', 'status', 'import_date_from', 'import_date_to']))
                    Không tìm thấy phôi văn bằng nào phù hợp với bộ lọc hiện tại.
                @else
                    Chưa có phôi văn bằng nào trong hệ thống.
                @endif
            </p>
            <div class="empty-actions">
                @if (request()->hasAny(['serial_number', 'type_id', 'status', 'import_date_from', 'import_date_to']))
                    @if ($importId)
                        <a href="{{ route('diploma-blanks.list-by-import', $importId) }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    @else
                        <a href="{{ route('diploma-blanks.index') }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>

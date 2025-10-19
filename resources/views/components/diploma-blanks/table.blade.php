{{-- Table component cho DiplomaBlank --}}
@props(['diplomaBlanks', 'importId' => null, 'damageReasons' => []])

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
                        <th class="th">Thông tin hư hỏng</th>
                        <th class="th">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="loading" class="loading-overlay hidden">
                        <td colspan="10" class="loading-cell">
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
                                <div class="damage-info">
                                    @php
                                        $isDamaged =
                                            $blank->status instanceof \App\Enums\DiplomaBlankStatus
                                                ? $blank->status === \App\Enums\DiplomaBlankStatus::DAMAGED
                                                : $blank->status === 'Damaged';
                                    @endphp

                                    @if ($isDamaged && $blank->damageReason)
                                        <div class="damage-details">
                                            <strong class="damage-reason">{{ $blank->damageReason->name }}</strong>
                                            @if ($blank->damage_date)
                                                <small class="damage-date text-muted d-block">
                                                    {{ $blank->damage_date->format('d/m/Y') }}
                                                </small>
                                            @endif
                                            @if ($blank->damage_description)
                                                <small class="damage-description text-muted d-block"
                                                    title="{{ $blank->damage_description }}">
                                                    {{ Str::limit($blank->damage_description, 30) }}
                                                </small>
                                            @endif
                                        </div>
                                    @elseif ($isDamaged)
                                        <span class="text-warning">Hư hỏng (chưa có chi tiết)</span>
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
                                        <button type="button" class="btn-action btn-delete" title="Báo hư hỏng"
                                            onclick="showMarkDamagedModal({{ $blank->diploma_blank_id }}, '{{ $blank->serial_number }}')">
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

    <!-- Modal báo hỏng phôi văn bằng -->
    <div id="markDamagedModal" class="modal" style="display: none;">
        <div class="modal__dialog">
            <div class="modal__overlay" onclick="hideMarkDamagedModal()"></div>
            <div class="modal__container">
                <div class="modal__content">
                    <div class="modal__header">
                        <div class="modal__title-wrapper">
                            <h3 class="modal__title">Báo hỏng phôi văn bằng</h3>
                        </div>
                        <button type="button" class="btn-close" onclick="hideMarkDamagedModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal__body">
                        <form id="markDamagedForm" method="POST" action="">
                            @csrf
                            <div class="form-group">
                                <label for="markDamaged_serial_number">Số sê-ri:</label>
                                <input type="text" id="markDamaged_serial_number" name="serial_number" readonly
                                    class="form-input readonly">
                            </div>

                            <div class="form-group">
                                <label for="damage_reason_id">Lý do hư hỏng: <span class="required">*</span></label>
                                <select id="damage_reason_id" name="damage_reason_id" class="form-select" required>
                                    <option value="">-- Chọn lý do hư hỏng --</option>
                                    @foreach ($damageReasons as $reason)
                                        <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="damage_description">Mô tả chi tiết (tùy chọn):</label>
                                <textarea id="damage_description" name="damage_description" class="form-textarea"
                                    placeholder="Mô tả chi tiết về tình trạng hư hỏng..." rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal__footer">
                        <button type="button" class="modal__footer--action-secondary"
                            onclick="hideMarkDamagedModal()">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </button>
                        <button type="button" class="modal__footer--action-primary" onclick="submitMarkDamaged()">
                            <i class="fas fa-exclamation-triangle"></i>
                            Báo hỏng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showMarkDamagedModal(diplomaBlankId, serialNumber) {
            document.getElementById('markDamaged_serial_number').value = serialNumber;
            document.getElementById('markDamagedForm').action = `/diploma-blanks/${diplomaBlankId}/mark-damaged`;
            document.getElementById('markDamagedModal').style.display = 'block';

            // Reset form
            document.getElementById('damage_reason_id').value = '';
            document.getElementById('damage_description').value = '';
        }

        function hideMarkDamagedModal() {
            document.getElementById('markDamagedModal').style.display = 'none';
        }

        function submitMarkDamaged() {
            const form = document.getElementById('markDamagedForm');
            const reasonSelect = document.getElementById('damage_reason_id');

            if (!reasonSelect.value) {
                alert('Vui lòng chọn lý do hư hỏng');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn báo hỏng phôi văn bằng này?')) {
                form.submit();
            }
        }

        // Đóng modal khi click overlay
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('markDamagedModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideMarkDamagedModal();
                    }
                });
            }
        });
    </script>
</div>

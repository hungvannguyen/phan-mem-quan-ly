<div class="data-table-container">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th">STT</th>
                    <th class="th">Tên loại văn bằng/chứng chỉ</th>
                    <th class="th">Mã tiền tố</th>
                    <th class="th">Số lượng phôi</th>
                    <th class="th">Ngày tạo</th>
                    <th class="th">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($types as $index => $type)
                    <tr class="table-row" onclick="toggleRowHighlight(this)">
                        <td class="td">{{ $types->firstItem() + $index }}</td>
                        <td class="td">
                            <div class="type-info">
                                <span class="type-name">{{ $type->type_name }}</span>
                            </div>
                        </td>
                        <td class="td">
                            <span class="prefix-badge">{{ $type->prefix }}</span>
                        </td>
                        <td class="td">
                            <span class="diploma-count">{{ number_format($type->diplomaBlanks()->count()) }}</span>
                        </td>
                        <td class="td">
                            <span class="date-text">{{ $type->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="td">
                            <div class="action-buttons">
                                @if (auth()->user()->hasPermission('settings.edit'))
                                    <a href="{{ route('settings.types.edit', $type->type_id) }}"
                                        class="btn-action btn-view" title="Chỉnh sửa">
                                        Sửa
                                    </a>

                                    <form action="{{ route('settings.types.destroy', $type->type_id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại văn bằng này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Xóa"
                                            {{ $type->diplomaBlanks()->count() > 0 ? 'disabled' : '' }}>
                                            Xóa
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted text-sm">Chỉ xem</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="td">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <h3 class="empty-title">Không có dữ liệu</h3>
                                <p class="empty-message">Hệ thống chưa có loại văn bằng hoặc chứng chỉ nào được thiết
                                    lập.</p>
                                @if (auth()->user()->hasPermission('settings.edit'))
                                    <div class="empty-actions">
                                        <a href="{{ route('settings.types.create') }}" class="btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Thêm loại mới
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Custom Pagination Section -->
<div class="table-pagination-wrapper">
    <x-pagination.custom :paginator="$types" item-name="loại văn bằng" label="Diploma Types Pagination Navigation"
        :per-page-options="[5, 10, 15, 25, 50]" />
</div>

<style>
    .type-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .type-name {
        font-weight: 500;
        color: #1a1a1a;
    }

    .prefix-badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        font-family: 'Courier New', monospace;
        box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
    }

    .diploma-count {
        font-weight: 600;
        color: #3b82f6;
        font-size: 1.05rem;
    }

    .date-text {
        color: #6b7280;
        font-size: 0.95rem;
    }
</style>

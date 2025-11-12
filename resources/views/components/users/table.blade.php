<div class="data-table-container">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th">Tên đăng nhập</th>
                    <th class="th">Họ và tên</th>
                    <th class="th">Email</th>
                    <th class="th">Trạng thái</th>
                    <th class="th">Ngày tạo</th>
                    <th class="th">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr id="loading" class="loading-overlay hidden">
                    <td colspan="6" class="loading-cell">
                        <div class="spinner"></div>
                        <span class="loading-text">Đang tải dữ liệu...</span>
                    </td>
                </tr>
                @forelse($users as $index => $user)
                    <tr class="table-row" data-user-id="{{ $user->user_id }}" onclick="toggleRowHighlight(this)">
                        <td class="td">
                            <div class="user-info">
                                <span class="user-username font-semibold">{{ $user->username }}</span>
                            </div>
                        </td>
                        <td class="td">
                            <span class="user-name">{{ $user->full_name }}</span>
                        </td>
                        <td class="td">
                            <span class="user-email">{{ $user->email }}</span>
                        </td>
                        <td class="td">
                            @if ($user->is_active)
                                <span class="status-badge status-completed">
                                    <i class="fas fa-check-circle"></i> Hoạt động
                                </span>
                            @else
                                <span class="status-badge status-failed">
                                    <i class="fas fa-times-circle"></i> Vô hiệu hóa
                                </span>
                            @endif
                        </td>
                        <td class="td">
                            @if ($user->created_at)
                                <span class="date-text">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td class="td">
                            <div class="action-buttons">
                                <a href="{{ route('user.edit', $user->user_id) }}" class="btn-action btn-view"
                                    title="Chỉnh sửa thông tin người dùng">
                                    Sửa
                                </a>

                                @if (Auth::id() !== $user->user_id)
                                    <form action="{{ route('user.toggle-status', $user->user_id) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn-action {{ $user->is_active ? 'btn-pause' : 'btn-start' }}"
                                            title="{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                            {{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                        </button>
                                    </form>

                                    <button class="btn-action btn-delete"
                                        onclick="confirmDeleteUser('{{ $user->user_id }}', '{{ $user->username }}')"
                                        title="Xóa người dùng">
                                        Xóa
                                    </button>
                                @else
                                    <span class="text-muted text-sm">(Bạn)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="td">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="empty-title">Không có dữ liệu</h3>
                                <p class="empty-message">Không tìm thấy người dùng nào phù hợp với bộ lọc hiện tại.</p>
                                <div class="empty-actions">
                                    <a href="{{ route('user.create') }}" class="btn-secondary">Thêm người dùng mới</a>
                                </div>
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
    <x-pagination.custom :paginator="$users" item-name="người dùng" label="Users Pagination Navigation"
        :per-page-options="[5, 10, 15, 25, 50]" />
</div>

<script>
    function toggleRowHighlight(row) {
        // Remove highlight from all rows
        document.querySelectorAll('.table-row').forEach(r => {
            r.classList.remove('row-highlighted');
        });
        // Add highlight to clicked row
        row.classList.add('row-highlighted');
    }

    function confirmDeleteUser(userId, username) {
        if (confirm(`⚠️ Bạn có chắc muốn xóa người dùng "${username}"?\n\nHành động này không thể hoàn tác!`)) {
            deleteUser(userId);
        }
    }

    function deleteUser(userId) {
        // Create form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/users/${userId}`;

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
</script>

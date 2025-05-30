@extends('layouts.default')

@section('content')
	<main class="embryo-management">
		<div class="form-section">
			<h2 class="section-title">Quản lý Phôi Văn bằng</h2>
			<form class="form">
				<div class="form-grid">
					<div class="form-group">
						<label for="batch-code">Mã lô</label>
						<input type="text" id="batch-code" name="batch-code" placeholder="Ví dụ: DOTPHOI_2025_01" required>
					</div>

					<div class="form-group">
						<label for="import-date">Ngày nhập</label>
						<input type="date" id="import-date" name="import-date" required>
					</div>

					<div class="form-group">
						<label for="initial-quantity">Số lượng ban đầu</label>
						<input type="number" id="initial-quantity" name="initial-quantity" placeholder="Nhập số lượng" required
										min="0">
					</div>

					<div class="form-group">
						<label for="remaining-quantity">Số lượng còn lại</label>
						<input type="number" id="remaining-quantity" name="remaining-quantity" placeholder="Số lượng còn lại"
										readonly>
					</div>

					<div class="form-group">
						<label for="error-quantity">Số lượng lỗi</label>
						<input type="number" id="error-quantity" name="error-quantity" placeholder="Số lượng lỗi" readonly>
					</div>

					<div class="form-group form-group-full">
						<label for="notes">Ghi chú</label>
						<textarea id="notes" name="notes" rows="3" placeholder="Ghi chú thêm về đợt nhập"></textarea>
					</div>
				</div>

				<div class="form-search-row">
					<button type="submit" class="btn btn-primary btn-search">Tìm kiếm</button>
				</div>

				<div class="action-container">
					<button class="btn btn-secondary">Thêm mới</button>
					<button class="btn btn-secondary">Xuất dữ liệu</button>
				</div>
			</form>
		</div>

		<div class="table-section">
			<div class="table-wrapper" id="table-data">
				@include('components.embryos.table')
			</div>
			<h3 class="section-subtitle mt-8">Lý do lỗi Phôi</h3>
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th class="w-10">ID</th>
						<th class="w-48">Tên lý do</th>
						<th class="w-full">Mô tả</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>DR01</td>
						<td>Rách</td>
						<td>Phôi bị rách do quá trình vận chuyển hoặc bảo quản không đúng cách.</td>
					</tr>
					<tr>
						<td>DR02</td>
						<td>Mờ chữ</td>
						<td>Chữ in trên phôi bị mờ, không rõ ràng, khó đọc.</td>
					</tr>
					<tr>
						<td>DR03</td>
						<td>Sai định dạng</td>
						<td>Phôi có kích thước hoặc định dạng không đúng chuẩn.</td>
					</tr>
					<tr>
						<td>DR04</td>
						<td>Thiếu thông tin</td>
						<td>Phôi bị thiếu một số thông tin cơ bản được in sẵn.</td>
					</tr>
					<tr>
						<td>DR05</td>
						<td>Hỏng tem bảo mật</td>
						<td>Tem bảo mật trên phôi bị hỏng, bong tróc hoặc có dấu hiệu bị can thiệp.</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</main>
@endsection
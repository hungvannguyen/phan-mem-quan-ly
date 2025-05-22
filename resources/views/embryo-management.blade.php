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
			</form>

			<div class="action-container">
				<button class="btn btn-secondary">Thêm mới</button>
				<button class="btn btn-secondary">Sửa</button>
				<button class="btn btn-secondary">Xoá</button>
				<button class="btn btn-secondary">Xuất dữ liệu</button>
			</div>
		</div>

		<div class="table-section">
			<h3 class="section-subtitle">Danh sách Lô Phôi</h3>
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th class="w-10">ID</th>
						<th class="w-40">Mã lô</th>
						<th class="w-32">Ngày nhập</th>
						<th class="w-32">SL Ban đầu</th>
						<th class="w-32">SL Còn lại</th>
						<th class="w-28">SL Lỗi</th>
						<th class="w-64">Ghi chú</th>
						<th class="w-40">Hành động</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>P001</td>
						<td>DOTPHOI_2025_01</td>
						<td>2025-01-15</td>
						<td>1000</td>
						<td>950</td>
						<td>50</td>
						<td>Lô phôi nhập từ nhà cung cấp A.</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Chi tiết</button>
							<button class="btn btn-table">Xóa</button>
						</td>
					</tr>
					<tr>
						<td>P002</td>
						<td>DOTPHOI_2025_02</td>
						<td>2025-02-20</td>
						<td>500</td>
						<td>480</td>
						<td>20</td>
						<td>Lô phôi nhập từ nhà cung cấp B.</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Chi tiết</button>
							<button class="btn btn-table">Xóa</button>
						</td>
					</tr>
					<tr>
						<td>P003</td>
						<td>DOTPHOI_2025_03</td>
						<td>2025-03-10</td>
						<td>1200</td>
						<td>1180</td>
						<td>20</td>
						<td>Lô phôi nhập từ nhà cung cấp A, đợt 2.</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Chi tiết</button>
							<button class="btn btn-table">Xóa</button>
						</td>
					</tr>
					<tr>
						<td>P004</td>
						<td>DOTPHOI_2025_04</td>
						<td>2025-04-05</td>
						<td>800</td>
						<td>790</td>
						<td>10</td>
						<td>Kiểm tra chất lượng trước khi nhập.</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Chi tiết</button>
							<button class="btn btn-table">Xóa</button>
						</td>
					</tr>
					<tr>
						<td>P005</td>
						<td>DOTPHOI_2025_05</td>
						<td>2025-05-01</td>
						<td>700</td>
						<td>670</td>
						<td>30</td>
						<td>Có một số phôi bị rách nhẹ.</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Chi tiết</button>
							<button class="btn btn-table">Xóa</button>
						</td>
					</tr>
					</tbody>
				</table>
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
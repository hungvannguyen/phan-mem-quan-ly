@extends('layouts.default')

@section('content')
	<main class="diploma-management">
		<form class="form">
			<div class="form-container">
				<div class="form-group">
					<label for="diploma-name">Họ và tên</label>
					<input type="text" id="diploma-name" name="diploma-name" placeholder="Nhập tên học viên" required>
				</div>

				<div class="form-group">
					<label for="diploma-year">Năm tốt nghiệp</label>
					<input type="text" id="diploma-year" name="diploma-year" placeholder="Nhập năm tốt nghiệp" required>
				</div>

				<div class="form-group">
					<label for="diploma-id">Số hiệu</label>
					<input type="text" id="diploma-id" name="diploma-id" placeholder="Nhập số hiệu của sinh viên" required>
				</div>

				<div class="form-group">
					<label for="diploma-course">Khoá</label>
					<input type="text" id="diploma-course" name="diploma-course" placeholder="Nhập Khoá" required>
				</div>

				<div class="form-group">
					<label for="diploma-branch">Ngành</label>
					<input type="text" id="diploma-branch" name="diploma-branch" placeholder="Nhập Ngành của học viên" required>
				</div>

				<div class="form-group">
					<label for="diploma-number">Số vào sổ</label>
					<input type="text" id="diploma-number" name="diploma-number" placeholder="Nhập ngành của học viên" required>
				</div>

			</div>

			<div class="form-group px-[20px]">
				<label for="diploma-select">Loại văn bằng</label>
				<select id="diploma-select" name="diploma-select">
					<option value="1">Văn bằng 2</option>
					<option value="2">Chứng chỉ</option>
					<option value="3">Chứng nhận</option>
					<option value="4">Giấy chứng nhận</option>
					<option value="5">Giấy chứng nhận tốt nghiệp</option>
				</select>

				<button class="btn btn-primary">Tìm kiếm</button>
			</div>

			<div class="action-container">
				<button class="btn btn-primary">Thêm mới</button>

				<button class="btn btn-primary">Sửa</button>

				<button class="btn btn-primary">Xoá</button>

				<button class="btn btn-primary">In xác nhận</button>

				<button class="btn btn-primary">In xác minh</button>

				<button class="btn btn-primary">Xuất dư liệu</button>
			</div>
		</form>

		<div class="overflow-x-auto border rounded-md p-2 bg-white">
			<table class="w-full table-auto border-collapse text-sm">
				<thead class="bg-green-600 text-white text-center">
				<tr>
					<th class="border px-2 py-1">#</th>
					<th class="border px-2 py-1">Họ và tên</th>
					<th class="border px-2 py-1">Ngày sinh</th>
					<th class="border px-2 py-1">Nơi sinh</th>
					<th class="border px-2 py-1">Giới tính</th>
					<th class="border px-2 py-1">Dân tộc</th>
					<th class="border px-2 py-1">Quốc tịch</th>
					<th class="border px-2 py-1">Ngành đào tạo</th>
					<th class="border px-2 py-1">Số hiệu</th>
					<th class="border px-2 py-1">Số vào sổ</th>
					<th class="border px-2 py-1">Tình trạng</th>
					<th class="border px-2 py-1">Chỉnh sửa</th>
					<th class="border px-2 py-1">Cấp lại</th>
					<th class="border px-2 py-1">Chi tiết</th>
				</tr>
				</thead>
				<tbody class="text-center">
				<tr>
					<td class="border px-2 py-1">1</td>
					<td class="border px-2 py-1">Nguyễn Văn A</td>
					<td class="border px-2 py-1">01/01/2000</td>
					<td class="border px-2 py-1">Hà Nội</td>
					<td class="border px-2 py-1">Nam</td>
					<td class="border px-2 py-1">Kinh</td>
					<td class="border px-2 py-1">Việt Nam</td>
					<td class="border px-2 py-1">CNTT</td>
					<td class="border px-2 py-1">123456</td>
					<td class="border px-2 py-1">789012</td>
					<td class="border px-2 py-1">Đã tốt nghiệp</td>
					<td class="border px-2 py-1">
						<button class="bg-green-700 hover:bg-green-800 text-white px-2 py-1 rounded">Sửa</button>
					</td>
					<td class="border px-2 py-1">
						<button class="bg-green-700 hover:bg-green-800 text-white px-2 py-1 rounded">Cấp lại</button>
					</td>
					<td class="border px-2 py-1">
						<button class="bg-green-700 hover:bg-green-800 text-white px-2 py-1 rounded">Chi tiết</button>
					</td>
				</tr>
				<!-- Thêm các dòng khác tại đây -->
				</tbody>
			</table>
		</div>

	</main>
@endsection
@extends('layouts.default')

@section('content')
	<main class="diploma-management">
		<div class="form-section">
			<form class="form">
				<div class="form-grid">
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

				<div class="form-search-row">
					<div class="form-group form-group-select">
						<label for="diploma-select">Loại văn bằng</label>
						<div class="form-select">
							<select id="diploma-select" name="diploma-select">
								<option value="1">Văn bằng 2</option>
								<option value="2">Chứng chỉ</option>
								<option value="3">Chứng nhận</option>
								<option value="4">Giấy chứng nhận</option>
								<option value="5">Giấy chứng nhận tốt nghiệp</option>
							</select>
							<button type="submit" class="btn btn-primary btn-search">Tìm kiếm</button>
						</div>
					</div>
				</div>

				<div class="action-container">
					<button class="btn btn-secondary">Thêm mới</button>
					<button class="btn btn-secondary">In xác nhận</button>
					<button class="btn btn-secondary">In xác minh</button>
					<button class="btn btn-secondary">Xuất dữ liệu</button>
				</div>
			</form>
		</div>

		<div class="table-section">
			<div class="table-wrapper">
				<table class="data-table">
					<thead>
					<tr>
						<th class="w-10">#</th>
						<th class="w-40">Họ và tên</th>
						<th class="w-32">Ngày sinh</th>
						<th class="w-32">Nơi sinh</th>
						<th class="w-24">Giới tính</th>
						<th class="w-24">Dân tộc</th>
						<th class="w-32">Quốc tịch</th>
						<th class="w-40">Ngành đào tạo</th>
						<th class="w-28">Số hiệu</th>
						<th class="w-28">Số vào sổ</th>
						<th class="w-32">Tình trạng</th>
						<th class="w-28">Hành động</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>1</td>
						<td>Nguyễn Văn A</td>
						<td>01/01/2000</td>
						<td>Hà Nội</td>
						<td>Nam</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>CNTT</td>
						<td>123456</td>
						<td>789012</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>2</td>
						<td>Trần Thị B</td>
						<td>05/03/1999</td>
						<td>TP.HCM</td>
						<td>Nữ</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Kinh tế</td>
						<td>654321</td>
						<td>210987</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>3</td>
						<td>Lê Văn C</td>
						<td>10/11/2001</td>
						<td>Đà Nẵng</td>
						<td>Nam</td>
						<td>Tày</td>
						<td>Việt Nam</td>
						<td>Luật</td>
						<td>987654</td>
						<td>345678</td>
						<td>Đang học</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>4</td>
						<td>Phạm Thị D</td>
						<td>20/07/2002</td>
						<td>Hải Phòng</td>
						<td>Nữ</td>
						<td>Mường</td>
						<td>Việt Nam</td>
						<td>Y học</td>
						<td>112233</td>
						<td>456789</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>5</td>
						<td>Hoàng Văn E</td>
						<td>15/09/1998</td>
						<td>Cần Thơ</td>
						<td>Nam</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Công nghệ thông tin</td>
						<td>445566</td>
						<td>987654</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>6</td>
						<td>Đặng Thị F</td>
						<td>25/04/2000</td>
						<td>Nghệ An</td>
						<td>Nữ</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Quản trị kinh doanh</td>
						<td>778899</td>
						<td>123456</td>
						<td>Đang học</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>7</td>
						<td>Vũ Văn G</td>
						<td>03/12/1997</td>
						<td>Huế</td>
						<td>Nam</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Kỹ thuật điện tử</td>
						<td>001122</td>
						<td>543210</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>8</td>
						<td>Nguyễn Thị H</td>
						<td>18/06/2003</td>
						<td>Đồng Nai</td>
						<td>Nữ</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Ngôn ngữ Anh</td>
						<td>334455</td>
						<td>678901</td>
						<td>Đang học</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>9</td>
						<td>Trần Văn I</td>
						<td>09/02/1996</td>
						<td>Bình Dương</td>
						<td>Nam</td>
						<td>Kinh</td>
						<td>Việt Nam</td>
						<td>Kiến trúc</td>
						<td>667788</td>
						<td>901234</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					<tr>
						<td>10</td>
						<td>Lý Thị K</td>
						<td>22/08/2000</td>
						<td>Quảng Ninh</td>
						<td>Nữ</td>
						<td>Hoa</td>
						<td>Việt Nam</td>
						<td>Du lịch</td>
						<td>990011</td>
						<td>234567</td>
						<td>Đã tốt nghiệp</td>
						<td>
							<button class="btn btn-table">Sửa</button>
							<button class="btn btn-table">Cấp lại</button>
							<button class="btn btn-table">Chi tiết</button>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</main>
@endsection

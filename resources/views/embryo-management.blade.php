@extends('layouts.default')

@section('content')
	<main class="embryo-management">
		<div class="form-section">
			<h2 class="section-title">Xuất phôi</h2>
			<form class="form">
				<div class="form-grid">
					<div class="form-group form-group-select">
						<label for="diploma-select">Loại phôi, văn bằng, chứng chỉ</label>
						<div class="form-select">
							<select id="diploma-select" name="diploma-select">
								<option value="1">Văn bằng 2</option>
								<option value="2">Chứng chỉ</option>
								<option value="3">Chứng nhận</option>
								<option value="4">Giấy chứng nhận</option>
								<option value="5">Giấy chứng nhận tốt nghiệp</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="batch-code">Năm</label>
						<input type="text" id="batch-code" name="batch-code" placeholder="Ví dụ: DOTPHOI_2025_01" required>
					</div>

					<div class="form-group">
						<label for="import-date">Khoá</label>
						<input type="text" id="import-date" name="import-date" required>
					</div>

					<div class="form-group">
						<label for="initial-quantity">Quyết định công nhận tốt nhiệp số</label>
						<input type="number" id="initial-quantity" name="initial-quantity" placeholder="Nhập số lượng" required
										min="0">
					</div>

					<div class="form-group">
						<label for="error-quantity">Ngày ban hành</label>
						<input type="date" id="dob" name="date_of_birth">
					</div>
				</div>
				<div id="form-container">
					<div class="form-grid --grid-4">
						<div class="form-group">
							<label for="batch-code">Số lượng</label>
							<input type="text" id="batch-code" name="batch-code" placeholder="Ví dụ: DOTPHOI_2025_01" required>
						</div>

						<div class="form-group">
							<label for="import-date">Từ Serial</label>
							<input type="text" id="import-date" name="import-date" required>
						</div>

						<div class="form-group">
							<label for="initial-quantity">Đến Serial</label>
							<input type="number" id="initial-quantity" name="initial-quantity" placeholder="Nhập số lượng" required
											min="0">
						</div>

						<div class="form-group form-group-select">
							<label for="diploma-select">Loại phôi, văn bằng, chứng chỉ</label>
							<div class="form-select">
								<select id="diploma-select" name="diploma-select">
									<option value="1">Văn bằng 2</option>
									<option value="2">Chứng chỉ</option>
									<option value="3">Chứng nhận</option>
									<option value="4">Giấy chứng nhận</option>
									<option value="5">Giấy chứng nhận tốt nghiệp</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="add-btn" onclick="addForm()">+ Thêm form</button>
			</form>
		</div>

		<div class="form-section">
			<h2 class="section-title">Hồi phôi</h2>
			<form class="form">
				<div class="form-grid">
					<div class="form-group">
						<label for="batch-code">Serial thu hồi</label>
						<input type="text" id="batch-code" name="batch-code" placeholder="Ví dụ: DOTPHOI_2025_01" required>
					</div>

					<div class="form-group">
						<label for="import-date">Lý do hồi</label>
						<input type="text" id="import-date" name="import-date" required>
					</div>

					<div class="form-group">
						<label for="initial-quantity">Serial phôi thay thế</label>
						<input type="number" id="initial-quantity" name="initial-quantity" placeholder="Nhập số lượng" required
										min="0">
					</div>
				</div>
				<button type="submit" class="btn btn-primary btn-search">Lưu</button>
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

	<script>
    function addForm() {
      const container = document.getElementById("form-container");
      const firstForm = container.querySelector(".form-grid");
      const newForm = firstForm.cloneNode(true);

      // Reset các giá trị trong input và select
      newForm.querySelectorAll("input, select").forEach(el => {
        el.value = "";
      });

      // Thêm nút xoá nếu chưa có
      if (!newForm.querySelector(".remove-btn")) {
        const removeBtn = document.createElement("button");
        removeBtn.textContent = "X";
        removeBtn.className = "remove-btn";
        removeBtn.onclick = function() {
          removeForm(this);
        };
        newForm.appendChild(removeBtn);
      }

      container.appendChild(newForm);
    }

    function removeForm(button) {
      const form = button.closest(".form-grid");
      const container = document.getElementById("form-container");

      // Đảm bảo không xoá form đầu tiên
      if (container.children.length > 1) {
        form.remove();
      } else {
        alert("Phải có ít nhất một form.");
      }
    }

	</script>
@endsection
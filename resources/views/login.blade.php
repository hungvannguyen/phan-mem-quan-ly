@extends('layouts.blank')

@section('content')
	<div class="login">
		<div class="header">
			<img src="https://hvannd.edu.vn/Content/images/banner.jpg" alt="Header Image">
		</div>
		<div class="login-body">
			<div class="title">Hệ thống quản lý văn bằng, chứng chỉ</div>

			<form action="/login" class="login-form" method="POST">
				@csrf
				<div class="form-group">
					<label for="username">Tên đăng nhập:</label>
					<div>
						<input type="text" class="form-control" id="username" placeholder="Nhập tên đăng nhập" name="email">
					</div>
				</div>
				<div class="form-group">
					<label for="password">Mật khẩu:</label>
					<div>
						<input type="password" class="form-control" id="password" placeholder="Nhập mật khẩu" name="password">
					</div>
				</div>

				<div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="remember" name="remember">
						<label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
					</div>
					<div class="form-submit">
						<button type="submit" class="btn btn-primary">Đăng nhập
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection
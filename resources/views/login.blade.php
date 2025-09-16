@extends('layouts.blank')

@section('content')
    <div class="auth-login">

        @include('components.header')

        <div class="auth-login-body">
            <div class="title">Hệ thống quản lý văn bằng, chứng chỉ</div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form action="/login" class="auth-login-form" method="POST">
                @csrf
                <div class="form-group">
                    <label for="login">Tên đăng nhập hoặc Email:</label>
                    <div>
                        <input type="text" class="form-control @error('login') is-invalid @enderror" id="login"
                            placeholder="Nhập tên đăng nhập hoặc email" name="login" value="{{ old('login') }}" required>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <div>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            placeholder="Nhập mật khẩu" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

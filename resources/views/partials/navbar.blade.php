<div class="navbar">
    @include('components.header')

    <nav class="navbar-menu">
        <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            Trang chủ
        </a>

        <a href="{{ route('diploma-blank-management') }}"
            class="menu-item {{ request()->routeIs('diploma-blank-management') ? 'active' : '' }}">
            Quản lý phôi văn bằng
        </a>

        <a href="{{ route('diploma-management') }}"
            class="menu-item {{ request()->routeIs('diploma-management') ? 'active' : '' }}">
            Quản lý văn bằng
        </a>

        <a href="{{ route('user-management') }}"
            class="menu-item {{ request()->routeIs('user-management') || request()->routeIs('user.*') ? 'active' : '' }}">
            Quản lý người dùng
        </a>

        <a class="menu-item">
            Cài đặt
        </a>
    </nav>

</div>

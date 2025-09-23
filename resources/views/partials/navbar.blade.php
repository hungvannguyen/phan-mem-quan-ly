<div class="navbar">
    @include('components.header')

    <nav class="navbar-menu">
        <a href="{{ route('diploma-blank-management') }}"
            class="menu-item {{ request()->routeIs('diploma-blank-management') ? 'active' : '' }}">
            Quản lý phôi văn bằng
        </a>

        <a href="{{ route('diploma-management') }}"
            class="menu-item {{ request()->routeIs('diploma-management') ? 'active' : '' }}">
            Quản lý văn bằng
        </a>

        <a class="menu-item">
            Quản lý người dùng
        </a>

        <a href="{{ route('certificate-management') }}"
            class="menu-item {{ request()->routeIs('certificate-management') ? 'active' : '' }}">
            Quản lý chứng chỉ
        </a>

        <a href="{{ route('settings') }}" class="menu-item {{ request()->routeIs('settings') ? 'active' : '' }}">
            Cài đặt
        </a>

        <a href="{{ route('about') }}" class="menu-item {{ request()->routeIs('about') ? 'active' : '' }}">
            Hệ thống quản lý văn bằng chứng chỉ
        </a>
    </nav>

</div>

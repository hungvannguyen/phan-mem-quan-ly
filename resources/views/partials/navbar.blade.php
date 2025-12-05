<div class="navbar">
    @include('components.header')

    <nav class="navbar-menu">
        <div class="menu-links">
            @if (auth()->user()->hasPermission('diplomas.view') || auth()->user()->hasPermission('certificates.view'))
                <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
            @endif

            @if (auth()->user()->hasPermission('diploma-blanks.view'))
                <a href="{{ route('diploma-blank-management') }}"
                    class="menu-item {{ request()->routeIs('diploma-blank-management') ? 'active' : '' }}">
                    <i class="fas fa-certificate"></i>
                    Quản lý phôi văn bằng
                </a>
            @endif

            @if (auth()->user()->hasPermission('diplomas.view'))
                <a href="{{ route('diploma-management') }}"
                    class="menu-item {{ request()->routeIs('diploma-management') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    Quản lý văn bằng
                </a>
            @endif

            @if (auth()->user()->hasPermission('users.view'))
                <a href="{{ route('user-management') }}"
                    class="menu-item {{ request()->routeIs('user-management') || request()->routeIs('user.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Quản lý người dùng
                </a>
            @endif

            @if (auth()->user()->hasPermission('settings.view'))
                <a href="{{ route('settings.index') }}"
                    class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="fas fa-list-ul"></i>
                    Quản lý danh mục
                </a>
            @endif
        </div>

        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span class="user-name">{{ auth()->user()->full_name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Đăng xuất">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </nav>

</div>

<div class="admin-header">
    <div class="admin-header-content">
        <h1 class="admin-header-title">@yield('page-title', '管理画面')</h1>
        <div class="admin-header-user">
            <span class="admin-header-user-name">{{ Auth::user()->name }}</span>
            <span class="admin-header-user-email">{{ Auth::user()->email }}</span>
        </div>
    </div>
</div>


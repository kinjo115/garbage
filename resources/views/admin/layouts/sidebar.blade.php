<div class="admin-sidebar">
    <div class="admin-sidebar-header">
        <h2 class="admin-sidebar-title">管理画面</h2>
    </div>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">📊</span>
            <span class="admin-sidebar-text">ダッシュボード</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="admin-sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">👥</span>
            <span class="admin-sidebar-text">ユーザー管理</span>
        </a>
        <a href="{{ route('admin.applications.index') }}" class="admin-sidebar-item {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">📋</span>
            <span class="admin-sidebar-text">申込み管理</span>
        </a>
        <a href="{{ route('admin.items.index') }}" class="admin-sidebar-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">📦</span>
            <span class="admin-sidebar-text">品目管理</span>
        </a>
        <a href="{{ route('admin.item-categories.index') }}" class="admin-sidebar-item {{ request()->routeIs('admin.item-categories.*') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">🏷️</span>
            <span class="admin-sidebar-text">カテゴリー管理</span>
        </a>
        <a href="{{ route('admin.payment-histories.index') }}" class="admin-sidebar-item {{ request()->routeIs('admin.payment-histories.*') ? 'active' : '' }}">
            <span class="admin-sidebar-icon">💳</span>
            <span class="admin-sidebar-text">決済履歴</span>
        </a>
        <div class="admin-sidebar-divider"></div>
        <form method="POST" action="{{ route('logout') }}" id="admin-logout-form" class="admin-sidebar-logout">
            @csrf
            <button type="button" id="admin-logout-button" class="admin-sidebar-item admin-sidebar-logout-btn">
                <span class="admin-sidebar-icon">🚪</span>
                <span class="admin-sidebar-text">ログアウト</span>
            </button>
        </form>
    </nav>
</div>

<script>
    $(document).ready(function() {
        $('#admin-logout-button').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'ログアウトしますか？',
                text: 'ログアウトすると、再度ログインが必要になります。',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#005FBE',
                cancelButtonColor: '#ED4141',
                confirmButtonText: 'ログアウトする',
                cancelButtonText: 'キャンセル',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#admin-logout-form').submit();
                }
            });
        });
    });
</script>


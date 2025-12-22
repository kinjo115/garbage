<div class="c-top-nav">
    <a href="#" class="c-top-nav-item">品目一覧表</a>
    <a href="#" class="c-top-nav-item">よくある質問</a>
    @if (Auth::check())
        <a href="{{ route('user.mypage') }}" class="c-top-nav-item">マイページ</a>
    @else
        <a href="{{ route('user.login') }}" class="c-top-nav-item">ログイン</a>
    @endif
</div>

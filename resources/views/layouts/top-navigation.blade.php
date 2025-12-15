<div class="c-top-nav">
    <a href="#" class="c-top-nav-item">品目一覧表</a>
    <a href="#" class="c-top-nav-item">よくある質問</a>
    <a href="#" class="c-top-nav-item" id="login-link">ログイン</a>
</div>

<!-- Modal -->
<div id="application-modal" class="c-modal">
    <div class="c-modal-overlay"></div>
    <div class="c-modal-content modal-lg modal-about-reg">
        <button class="c-modal-close" id="modal-close">&times;</button>
        <div class="c-modal-header">
            <h2 class="c-modal-title c-title text-center">申込みについて</h2>
        </div>
        <div class="c-modal-body">
            <div class="mt-6">
                <p class="text-center">はじめて申込む場合</p>

                <div class="mt-5">
                    <a href="{{ route('user.register') }}" class="c-button btn-416FED">新規登録</a>
                </div>
            </div>
            <div class="mt-10">
                <div class="text-center">
                    <p>すでに会員登録を行っている場合や</p>
                    <p>受付番号を用いて申込み内容に修正を行う方</p>
                </div>
                <div class="p-alert">
                    <p>・品目の追加は、収集日の７日前までに行ってください。</p>
                    <p>・品目の減少は、収集日の１日前までに行ってください。</p>
                    <p>・受付の取消は、収集日の１日前までに行ってください。</p>
                </div>
                <div class="mt-5">
                    <a href="{{ route('user.login') }}" class="c-button btn-ED4141">ログイン</a>
                </div>
            </div>
        </div>
    </div>
</div>

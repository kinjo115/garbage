@extends('layouts.app')

@section('meta')
    <title>品目選択 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="品目選択,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ $tempUser->token ? route('guest.register.confirm.store.map', ['token' => $tempUser->token]) : route('user.items.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="breadcrumbs">
                <div class="breadcrumbs-item">
                    <a href="{{ route('home') }}">ホーム</a>
                </div>
                @if($tempUser->token)
                    <div class="breadcrumbs-item">
                        <span>品目選択</span>
                    </div>
                @else
                    <div class="breadcrumbs-item">
                        <a href="{{ route('user.mypage') }}">マイページ</a>
                    </div>
                    <div class="breadcrumbs-item">
                        <span>品目選択</span>
                    </div>
                @endif
            </div>

            <form id="items-form" method="POST" action="{{ $tempUser->token ? route('guest.item.store', ['token' => $tempUser->token]) : (isset($selectedItem) ? route('user.items.update-items', ['id' => $selectedItem->id]) : route('user.items.store')) }}"
                data-initial-items='@json($initialSelectedItems ?? [])'>
                @csrf
                <input type="hidden" name="items_json" id="items-json">

                <div class="page-content form-content">
                    <div class="page-header">
                        <h1 class="page-title">{{ isset($selectedItem) ? '品目変更' : '品目選択' }}</h1>
                    </div>
                    @if(isset($selectedItem))
                        <div class="form-notification">
                            @if($selectedItem->confirm_status === \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED)
                                この申込みはキャンセルされています。
                            @else
                                既存の申込みの品目を変更できます。
                            @endif
                        </div>
                    @endif
                    <div class="form-description">
                        <p class="text-E20000 text-bold">【注意事項】</p>
                        <br>
                        <p>・分類を選択することで、絞込みが可能です。</p>
                        <p>・フリーワードを入力することで、分類の絞込みから更に品目を絞り込むことが可能です。</p>
                        <p>・品目名をクリックすると、一覧に追加されます。</p>
                        <p>・追加された品目に対して個数を設定し、品目全体を削除できます。</p>
                        <p>・複数の品目を追加した場合、合計金額が表示されます。</p>
                        <p>・品目が見当たらないときはインターネット受付を中止して、「粗大ごみ受付センター」(電話番号はこちら)に電話でお申込みください。</p>
                        <p>・1品目の重さが100kgを超えるものは、収集困難のためお出しいただくことができません。</p>
                    </div>

                    {{-- 選択済み品目一覧 --}}
                    <div class="selected-items mt-16">
                        <div class="selected-items-header">
                            <h2 class="selected-items-title">選択済み品目</h2>
                        </div>
                        <div class="selected-items-body">
                            <div class="selected-items-wrapper" id="selected-items-wrapper">
                                {{-- JSで動的に挿入 --}}
                            </div>
                            <div class="total-content mt-6">
                                <div class="total-content-title">合計金額</div>
                                <div class="total-content-amount" id="total-content-amount">0円 <span
                                        class="text-sm">(0個)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 品目一覧 --}}
                    <div class="mt-16">
                        <div class="item-list-wrapper" id="item-list-wrapper">
                            @foreach ($items as $item)
                                <div class="item-list-item" data-item-id="{{ $item->id }}"
                                    data-item-name="{{ $item->name }}" data-item-price="{{ $item->price }}">
                                    <div class="flex items-end justify-between">
                                        <button type="button" class="item-list-item-name js-add-item">
                                            {{ $item->name }}
                                        </button>
                                        <div class="item-list-item-price">{{ $item->price }}円</div>
                                    </div>
                                    <div class="category-wrapper">
                                        <div class="category-item">{{ $item->itemCategory->name }}</div>
                                    </div>
                                    <div class="description-wrapper">
                                        <div class="label">説明表示</div>
                                        <div class="description-item">{{ $item->description }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="c-button btn-416FED" id="submit-btn" {{ isset($selectedItem) && $selectedItem->confirm_status === \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED ? 'disabled' : '' }}>{{ isset($selectedItem) ? '品目を更新する' : '申込内容の確認に進む' }}</button>
                    </div>
                    <div class="md:mt-16 mt-10 flex justify-center">
                        @if($tempUser->token)
                            <a href="{{ route('guest.register.confirm.store.map', ['token' => $tempUser->token]) }}"
                                class="c-btn-black">戻る</a>
                        @elseif(isset($selectedItem))
                            <a href="{{ route('user.history.index') }}"
                                class="c-btn-black">戻る</a>
                        @else
                            <a href="{{ route('user.info.map') }}"
                                class="c-btn-black">戻る</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($selectedItem))
    <script>
        $(document).ready(function() {
            // 既存申込みの場合、品目変更を検知してボタンを有効/無効化
            const $itemsForm = $('#items-form');
            const $submitBtn = $('#submit-btn');
            
            // 初期選択データを保存
            let initialSelectedItems = [];
            const initialItemsAttr = $itemsForm.attr('data-initial-items');
            if (initialItemsAttr) {
                try {
                    const parsed = JSON.parse(initialItemsAttr);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        initialSelectedItems = parsed.map(function(item) {
                            return {
                                id: item.id,
                                quantity: item.quantity,
                            };
                        }).sort(function(a, b) { return a.id - b.id; });
                    }
                } catch (e) {
                    console.warn('初期品目データのパースに失敗しました', e);
                }
            }

            // 現在の選択データと初期データを比較
            function checkItemsChanged() {
                if (initialSelectedItems.length === 0 && selectedItems.length === 0) {
                    return false;
                }

                if (initialSelectedItems.length !== selectedItems.length) {
                    return true;
                }

                const currentItems = selectedItems.map(function(item) {
                    return {
                        id: item.id,
                        quantity: item.quantity,
                    };
                }).sort(function(a, b) { return a.id - b.id; });

                // 配列を比較
                if (JSON.stringify(initialSelectedItems) !== JSON.stringify(currentItems)) {
                    return true;
                }

                return false;
            }

            // ボタンの有効/無効を更新
            function updateSubmitButton() {
                const hasChanged = checkItemsChanged();
                const hasItems = selectedItems.length > 0;
                const isCancelled = {{ isset($selectedItem) && $selectedItem->confirm_status === \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED ? 'true' : 'false' }};

                if (isCancelled || !hasItems || !hasChanged) {
                    $submitBtn.prop('disabled', true);
                } else {
                    $submitBtn.prop('disabled', false);
                }
            }

            // 品目変更時にボタンの状態を更新
            $(document).on('itemChanged', function() {
                updateSubmitButton();
            });

            // 初期状態でボタンを無効化
            updateSubmitButton();

            // 既存のrenderSelectedItems関数をオーバーライドして、変更検知を追加
            const originalRenderSelectedItems = window.renderSelectedItems;
            if (typeof originalRenderSelectedItems === 'function') {
                window.renderSelectedItems = function() {
                    originalRenderSelectedItems();
                    $(document).trigger('itemChanged');
                };
            }
        });
    </script>
    @endif
@endsection

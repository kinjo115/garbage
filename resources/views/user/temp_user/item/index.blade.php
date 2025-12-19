@extends('layouts.app')

@section('meta')
    <title>品目選択 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="品目選択,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="品目選択 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('guest.register.confirm.store.map', ['token' => $tempUser->token]) }}">
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
                <div class="breadcrumbs-item">
                    <span>品目選択</span>
                </div>
            </div>

            <form id="items-form" method="POST" action="{{ route('guest.item.store', ['token' => $tempUser->token]) }}"
                data-initial-items='@json($initialSelectedItems ?? [])'>
                @csrf
                <input type="hidden" name="items_json" id="items-json">

                <div class="page-content form-content">
                    <div class="page-header">
                        <h1 class="page-title">品目選択</h1>
                    </div>
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
                        <button type="submit" class="c-button btn-416FED">申込内容の確認に進む</button>
                    </div>
                    <div class="md:mt-16 mt-10 flex justify-center">
                        <a href="{{ route('guest.register.confirm.store.map', ['token' => $tempUser->token]) }}"
                            class="c-btn-black">戻る</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('admin.layouts.app')

@section('meta')
    <title>カテゴリー管理 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'カテゴリー管理')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">カテゴリー一覧</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="{{ route('admin.item-categories.create') }}" class="admin-btn admin-btn-primary">新規作成</a>
                        <form method="GET" action="{{ route('admin.item-categories.index') }}" style="display: flex; gap: 10px;">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="検索..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <button type="submit" class="admin-btn admin-btn-primary">検索</button>
                            @if(filled(request('search')))
                                <a href="{{ route('admin.item-categories.index') }}" class="admin-btn admin-btn-secondary">クリア</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                @if(session('success'))
                    <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                        {{ session('error') }}
                    </div>
                @endif
                @if($categories->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>カテゴリー名</th>
                                <th>親カテゴリー</th>
                                <th>品目数</th>
                                <th>並び順</th>
                                <th>登録日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->parent->name ?? '-' }}</td>
                                    <td>{{ $category->items->count() }}</td>
                                    <td>{{ $category->sort }}</td>
                                    <td>{{ $category->created_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="{{ route('admin.item-categories.edit', $category->id) }}" class="admin-btn admin-btn-sm admin-btn-primary">編集</a>
                                            <form method="POST" action="{{ route('admin.item-categories.destroy', $category->id) }}" class="delete-category-form" style="display: inline;" data-category-name="{{ $category->name }}" data-item-count="{{ $category->items->count() }}" data-children-count="{{ $category->children->count() }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="admin-btn admin-btn-sm admin-btn-danger delete-category-btn">削除</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="admin-empty">カテゴリーが見つかりませんでした</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Script -->
    <script>
        $(document).ready(function() {
            $('.delete-category-btn').on('click', function(e) {
                e.preventDefault();
                var $form = $(this).closest('form');
                var categoryName = $form.data('category-name') || 'このカテゴリー';
                var itemCount = parseInt($form.data('item-count')) || 0;
                var childrenCount = parseInt($form.data('children-count')) || 0;

                var warningText = categoryName + 'を削除してもよろしいですか？';
                if (itemCount > 0 || childrenCount > 0) {
                    warningText += '\n\n';
                    if (childrenCount > 0) {
                        warningText += '※ 子カテゴリーが' + childrenCount + '個存在します。\n';
                    }
                    if (itemCount > 0) {
                        warningText += '※ このカテゴリーに紐づいている品目が' + itemCount + '個存在します。\n';
                    }
                    warningText += '削除できない場合があります。';
                }

                Swal.fire({
                    title: '削除しますか？',
                    text: warningText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ED4141',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '削除する',
                    cancelButtonText: 'キャンセル',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $form.submit();
                    }
                });
            });
        });
    </script>
@endsection


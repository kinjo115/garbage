@extends('admin.layouts.app')

@section('meta')
    <title>品目管理 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '品目管理')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">品目一覧</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="{{ route('admin.items.create') }}" class="admin-btn admin-btn-primary">新規作成</a>
                        <form method="GET" action="{{ route('admin.items.index') }}" style="display: flex; gap: 10px;">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="検索..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="">すべてのステータス</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>非アクティブ</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>アクティブ</option>
                            </select>
                            <select name="category_id" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="">すべてのカテゴリー</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="admin-btn admin-btn-primary">検索</button>
                            @if(filled(request('search')) || filled(request('status')) || filled(request('category_id')))
                                <a href="{{ route('admin.items.index') }}" class="admin-btn admin-btn-secondary">クリア</a>
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
                @php
                    $hasFilters = filled(request('search')) || filled(request('status')) || filled(request('category_id'));
                @endphp
                @if($items->count() > 0)
                    @if($hasFilters)
                        <div style="background-color: #fff3cd; color: #856404; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                            <strong>注意:</strong> 検索・フィルターが適用されているため、ドラッグ&ドロップによる並び順の変更はできません。すべての品目を表示するには、フィルターをクリアしてください。
                        </div>
                    @endif
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">{{ !$hasFilters ? '並び替え' : '' }}</th>
                                <th>ID</th>
                                <th>画像</th>
                                <th>品目名</th>
                                <th>カテゴリー</th>
                                <th>価格</th>
                                <th>ステータス</th>
                                <th>並び順</th>
                                <th>登録日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-items" class="{{ !$hasFilters ? 'sortable-tbody' : '' }}">
                            @foreach($items as $item)
                                <tr data-item-id="{{ $item->id }}" data-sort="{{ $item->sort }}">
                                    <td class="drag-handle" style="cursor: {{ !$hasFilters ? 'move' : 'default' }}; text-align: center;">
                                        @if(!$hasFilters)
                                            <span style="font-size: 18px;">☰</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->itemCategory->name ?? '-' }}</td>
                                    <td>{{ number_format($item->price) }}円</td>
                                    <td>
                                        @if($item->status == 0)
                                            <span style="color: #999;">非アクティブ</span>
                                        @elseif($item->status == 1)
                                            <span style="color: #28a745;">アクティブ</span>
                                        @elseif($item->status == 2)
                                            <span style="color: #dc3545;">売り切れ</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->sort }}</td>
                                    <td>{{ $item->created_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="{{ route('admin.items.edit', $item->id) }}" class="admin-btn admin-btn-sm admin-btn-primary">編集</a>
                                            <form method="POST" action="{{ route('admin.items.destroy', $item->id) }}" class="delete-item-form" style="display: inline;" data-item-name="{{ $item->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="admin-btn admin-btn-sm admin-btn-danger delete-item-btn">削除</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="admin-empty">品目が見つかりませんでした</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Script -->
    <script>
        $(document).ready(function() {
            $('.delete-item-btn').on('click', function(e) {
                e.preventDefault();
                var $form = $(this).closest('form');
                var itemName = $form.data('item-name') || 'この品目';

                Swal.fire({
                    title: '削除しますか？',
                    text: itemName + 'を削除してもよろしいですか？',
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

    @if(!$hasFilters)
        <!-- jQuery UI Sortable -->
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

        <style>
            .sortable-tbody tr {
                cursor: move;
            }
            .sortable-tbody tr.ui-sortable-helper {
                background-color: #f0f0f0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .sortable-tbody tr.ui-sortable-placeholder {
                height: 50px;
                background-color: #e0e0e0;
                visibility: visible !important;
                border: 2px dashed #999;
            }
            .drag-handle {
                user-select: none;
            }
        </style>

        <script>
            $(document).ready(function() {
                var $tbody = $('#sortable-items');

                $tbody.sortable({
                    handle: '.drag-handle',
                    axis: 'y',
                    placeholder: 'ui-sortable-placeholder',
                    helper: function(e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            $(this).width($originals.eq(index).width());
                        });
                        return $helper;
                    },
                    update: function(event, ui) {
                        var items = [];
                        $tbody.find('tr').each(function(index) {
                            var $row = $(this);
                            items.push({
                                id: $row.data('item-id'),
                                sort: index + 1
                            });
                        });

                        // AJAXで並び順を更新
                        $.ajax({
                            url: '{{ route("admin.items.update-order") }}',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                items: items
                            },
                            success: function(response) {
                                if (response.success) {
                                    // 並び順の表示を更新
                                    $tbody.find('tr').each(function(index) {
                                        $(this).find('td:eq(7)').text(index + 1);
                                        $(this).data('sort', index + 1);
                                    });

                                    // 成功メッセージを表示
                                    Swal.fire({
                                        icon: 'success',
                                        title: '成功',
                                        text: response.message || '並び順を更新しました。',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function(xhr) {
                                var message = '並び順の更新に失敗しました。';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'エラー',
                                    text: message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });

                                // エラー時はページをリロードして元に戻す
                                location.reload();
                            }
                        });
                    }
                });
            });
        </script>
    @endif
@endsection


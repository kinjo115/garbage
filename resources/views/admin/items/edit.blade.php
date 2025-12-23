@extends('admin.layouts.app')

@section('meta')
    <title>品目編集 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '品目編集')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">品目情報を編集</h3>
                    <a href="{{ route('admin.items.index') }}" class="admin-btn admin-btn-secondary">戻る</a>
                </div>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.items.update', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">基本情報</h4>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">品目名 <span style="color: #ED4141;">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $item->name) }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                @error('name')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">カテゴリー</label>
                                <select name="item_category_id" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="">カテゴリーを選択</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('item_category_id', $item->item_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_category_id')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">価格 <span style="color: #ED4141;">*</span></label>
                                <input type="text" name="price" value="{{ old('price', $item->price) }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" placeholder="例: 1000">
                                @error('price')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">ステータス <span style="color: #ED4141;">*</span></label>
                                <select name="status" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="0" {{ old('status', $item->status) == '0' ? 'selected' : '' }}>非アクティブ</option>
                                    <option value="1" {{ old('status', $item->status) == '1' ? 'selected' : '' }}>アクティブ</option>
                                </select>
                                @error('status')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">並び順</label>
                                <input type="number" name="sort" value="{{ old('sort', $item->sort) }}" min="0" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                @error('sort')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div> -->
                        </div>
                        <div>
                            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">詳細情報</h4>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">説明</label>
                                <textarea name="description" rows="6" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;">{{ old('description', $item->description) }}</textarea>
                                @error('description')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                           <!--  <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">現在の画像</label>
                                @if($item->image)
                                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" style="max-width: 200px; max-height: 200px; border-radius: 4px; margin-bottom: 10px;">
                                @else
                                    <p style="color: #999;">画像が設定されていません</p>
                                @endif
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">画像を変更</label>
                                <input type="file" name="image" accept="image/*" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <p style="font-size: 12px; color: #666; margin-top: 5px;">JPEG, PNG, JPG, GIF形式、最大2MB（変更しない場合は空欄のまま）</p>
                                @error('image')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div> -->
                        </div>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="submit" class="admin-btn admin-btn-primary">更新</button>
                        <a href="{{ route('admin.items.index') }}" class="admin-btn admin-btn-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


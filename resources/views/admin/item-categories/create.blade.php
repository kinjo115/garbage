@extends('admin.layouts.app')

@section('meta')
    <title>カテゴリー作成 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'カテゴリー作成')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">カテゴリーを作成</h3>
                    <a href="{{ route('admin.item-categories.index') }}" class="admin-btn admin-btn-secondary">戻る</a>
                </div>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.item-categories.store') }}">
                    @csrf
                    
                    <div style="max-width: 600px;">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">カテゴリー名 <span style="color: #ED4141;">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            @error('name')
                                <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">親カテゴリー</label>
                            <select name="parent_id" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="">親カテゴリーなし</option>
                                @foreach($parentCategories as $parentCategory)
                                    <option value="{{ $parentCategory->id }}" {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>{{ $parentCategory->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">並び順</label>
                            <input type="number" name="sort" value="{{ old('sort', 0) }}" min="0" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            @error('sort')
                                <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="submit" class="admin-btn admin-btn-primary">作成</button>
                        <a href="{{ route('admin.item-categories.index') }}" class="admin-btn admin-btn-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


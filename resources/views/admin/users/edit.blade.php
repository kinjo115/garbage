@extends('admin.layouts.app')

@section('meta')
    <title>ユーザー編集 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'ユーザー編集')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">ユーザー情報を編集</h3>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="admin-btn admin-btn-secondary">戻る</a>
                </div>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">基本情報</h4>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">メールアドレス</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                @error('email')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">名前</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                @error('name')
                                    <span style="color: #ED4141; font-size: 12px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @if($user->userInfo)
                            <div>
                                <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">詳細情報</h4>
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">姓</label>
                                    <input type="text" name="last_name" value="{{ old('last_name', $user->userInfo->last_name) }}" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">名</label>
                                    <input type="text" name="first_name" value="{{ old('first_name', $user->userInfo->first_name) }}" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">電話番号</label>
                                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->userInfo->phone_number) }}" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="submit" class="admin-btn admin-btn-primary">更新</button>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="admin-btn admin-btn-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


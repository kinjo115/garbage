@extends('admin.layouts.app')

@section('meta')
    <title>ユーザー管理 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'ユーザー管理')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">ユーザー一覧</h3>
                    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 10px;">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="検索..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <button type="submit" class="admin-btn admin-btn-primary">検索</button>
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">クリア</a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="admin-card-body">
                @if($users->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>メールアドレス</th>
                                <th>名前</th>
                                <th>電話番号</th>
                                <th>登録日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->userInfo)
                                            {{ $user->userInfo->last_name }} {{ $user->userInfo->first_name }}
                                        @else
                                            {{ $user->name }}
                                        @endif
                                    </td>
                                    <td>{{ $user->userInfo->phone_number ?? '-' }}</td>
                                    <td>{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="admin-btn admin-btn-sm admin-btn-primary">詳細</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="margin-top: 20px;">
                        {{ $users->links() }}
                    </div>
                @else
                    <p class="admin-empty">ユーザーが見つかりませんでした</p>
                @endif
            </div>
        </div>
    </div>
@endsection


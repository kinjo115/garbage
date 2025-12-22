<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * ユーザー一覧を表示
     */
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE['USER'])->with('userInfo');

        // 検索機能
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('userInfo', function($q) use ($search) {
                      $q->where('last_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * ユーザー詳細を表示
     */
    public function show($id)
    {
        $user = User::with('userInfo')->findOrFail($id);
        $applications = \App\Models\SelectedItem::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.show', compact('user', 'applications'));
    }

    /**
     * ユーザー編集画面を表示
     */
    public function edit($id)
    {
        $user = User::with('userInfo')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * ユーザー情報を更新
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'email' => $request->email,
                'name' => $request->name,
            ]);

            // UserInfoが存在する場合は更新
            if ($user->userInfo) {
                $user->userInfo->update($request->only([
                    'last_name', 'first_name', 'phone_number', 'postal_code',
                    'prefecture_id', 'city', 'town', 'chome', 'building_number',
                    'house_number', 'building_name', 'apartment_name', 'apartment_number',
                    'emergency_contact'
                ]));
            }

            DB::commit();

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'ユーザー情報を更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'ユーザー情報の更新に失敗しました。')
                ->withInput();
        }
    }

    /**
     * ユーザーを削除
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // UserInfoを削除
            if ($user->userInfo) {
                $user->userInfo->delete();
            }

            // ユーザーを削除
            $user->delete();

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'ユーザーを削除しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'ユーザーの削除に失敗しました。');
        }
    }
}


<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('user.mypage.index');
    }

    public function newApplication()
    {
        // TODO: Implement new application page
        return view('user.mypage.new_application');
    }

    public function applicationHistory()
    {
        // TODO: Implement application history page
        return view('user.mypage.application_history');
    }

    public function editProfile()
    {
        // TODO: Implement profile edit page
        return view('user.mypage.edit_profile');
    }

    public function editPassword()
    {
        // TODO: Implement password edit page
        return view('user.mypage.edit_password');
    }

    public function withdraw()
    {
        // TODO: Implement withdraw page
        return view('user.mypage.withdraw');
    }
}

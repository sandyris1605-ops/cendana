<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login() {
        if (auth()->check()) return redirect('/admin');
        return view('auth.login');
    }
    public function doLogin(Request $r) {
        $r->validate(['email'=>'required|email','password'=>'required']);
        if (auth()->attempt($r->only('email','password'), $r->filled('remember'))) {
            $r->session()->regenerate();
            ActivityLog::create(['user_id'=>auth()->id(),'aksi'=>'Login','modul'=>'auth']);
            return redirect()->intended('/admin');
        }
        return back()->withErrors(['email'=>'Email / password salah.'])->withInput();
    }
    public function logout(Request $r) {
        ActivityLog::create(['user_id'=>auth()->id(),'aksi'=>'Logout','modul'=>'auth']);
        auth()->logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect('/login');
    }
}

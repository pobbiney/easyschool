<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentAccount;
use App\Models\SchoolSetting;
use App\Support\GhanaPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    public function showLogin()
    {
        if (auth('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.auth.login', [
            'school' => SchoolSetting::current(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'password' => 'required|string',
        ]);

        $phone = GhanaPhone::normalize($validated['phone']);

        if (! $phone) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Enter a valid phone number.');
        }

        $parent = ParentAccount::query()->where('phone', $phone)->first();

        if (! $parent || ! $parent->isActive()) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Phone number or password is incorrect.');
        }

        if (! Hash::check($validated['password'], $parent->password)) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Phone number or password is incorrect.');
        }

        Auth::guard('parent')->login($parent, $request->boolean('remember'));
        $parent->update(['last_login_at' => now()]);

        return redirect()->intended(route('parent.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('parent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('parent.login')->with('message_success', 'Signed out successfully.');
    }
}

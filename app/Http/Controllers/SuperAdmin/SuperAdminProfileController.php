<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SuperAdminProfileController extends Controller
{
    public function show()
    {
        return view('super-admin.profile.index', [
            'admin' => Auth::guard('super_admin')->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('super_admin')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->with('message_error', 'Current password is incorrect.');
        }

        $admin->update([
            'password' => $validated['new_password'],
        ]);

        return back()->with('message_success', 'Password updated successfully.');
    }
}

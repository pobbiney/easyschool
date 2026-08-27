<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class SuperAdminUserController extends Controller
{
    public function index()
    {
        return view('super-admin.admins.index', [
            'admins' => SuperAdmin::query()->orderBy('name')->get(),
            'currentAdminId' => Auth::guard('super_admin')->id(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:super_admins,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        SuperAdmin::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => SuperAdmin::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('super-admin.admins')
            ->with('message_success', 'Super admin account created for '.$validated['email'].'.');
    }

    public function toggleStatus(SuperAdmin $superAdmin)
    {
        if ($superAdmin->id === Auth::guard('super_admin')->id()) {
            return back()->with('message_error', 'You cannot change your own account status.');
        }

        $superAdmin->update([
            'status' => $superAdmin->isActive() ? SuperAdmin::STATUS_INACTIVE : SuperAdmin::STATUS_ACTIVE,
        ]);

        $label = $superAdmin->isActive() ? 'activated' : 'deactivated';

        return back()->with('message_success', "{$superAdmin->name} has been {$label}.");
    }
}

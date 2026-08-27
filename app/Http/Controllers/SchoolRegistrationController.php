<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\Tenant\SchoolActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class SchoolRegistrationController extends Controller
{
    public function create()
    {
        return view('school-registration.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_phone' => 'nullable|string|max:50',
            'admin_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $school = School::query()->create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'admin_phone' => $validated['admin_phone'] ?? null,
            'admin_password' => $validated['admin_password'],
            'status' => School::STATUS_PENDING,
        ]);

        app(SchoolActivityLogger::class)->log(
            action: 'school.registration_submitted',
            description: 'New school registration submitted',
            payload: ['school_id' => $school->id, 'name' => $school->name],
            schoolId: $school->id,
        );

        return view('school-registration.success', compact('school'));
    }
}

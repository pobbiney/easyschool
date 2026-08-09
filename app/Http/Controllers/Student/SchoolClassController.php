<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    public function index()
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return view('student.school-classes', [
            'schoolClasses' => $schoolClasses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'status' => 'required',
        ]);

        if (SchoolClass::where('name', trim($request->name))->count() > 0) {
            return back()->with('message_error', 'This class already exists.');
        }

        $class = new SchoolClass();
        $class->name = trim($request->name);
        $class->status = trim($request->status);
        $class->created_by = Auth::id();
        $class->save();

        return back()->with('message_success', 'Class added successfully.');
    }

    public function show($id)
    {
        $class = SchoolClass::findOrFail($id);

        return response()->json($class);
    }

    public function update(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'name' => 'required|string|max:100',
            'status' => 'required',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);

        $nameExists = SchoolClass::where('name', trim($request->name))
            ->where('id', '!=', $request->class_id)
            ->count();

        if ($nameExists > 0) {
            return back()->with('message_error', 'This class already exists.');
        }

        $class->name = trim($request->name);
        $class->status = trim($request->status);
        $class->updated_by = Auth::id();
        $class->save();

        return back()->with('message_success', 'Class updated successfully.');
    }
}

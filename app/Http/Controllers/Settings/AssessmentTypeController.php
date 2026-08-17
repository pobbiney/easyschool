<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AssessmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssessmentTypeController extends Controller
{
    public function index()
    {
        $assessmentTypes = AssessmentType::query()
            ->withCount('assessments')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('settings.assessment-types', [
            'assessmentTypes' => $assessmentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', array_keys(AssessmentType::CATEGORIES)),
            'sort_order' => 'required|integer|min:1|max:99',
            'max_number' => 'required|integer|min:1|max:999',
            'total_score' => 'required|numeric|min:1|max:9999',
            'status' => 'required|in:Active,Inactive',
        ]);

        $name = trim($request->name);

        if (AssessmentType::where('name', $name)->exists()) {
            return back()->with('message_error', 'This assessment type already exists.');
        }

        $type = new AssessmentType();
        $type->name = $name;
        $type->slug = $this->makeUniqueSlug($name);
        $type->category = trim($request->category);
        $type->sort_order = (int) $request->sort_order;
        $type->max_number = (int) $request->max_number;
        $type->total_score = round((float) $request->total_score, 2);
        $type->status = trim($request->status);
        $type->created_by = Auth::id();
        $type->save();

        return back()->with('message_success', 'Assessment type added successfully.');
    }

    public function show($id)
    {
        return response()->json(AssessmentType::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', array_keys(AssessmentType::CATEGORIES)),
            'sort_order' => 'required|integer|min:1|max:99',
            'max_number' => 'required|integer|min:1|max:999',
            'total_score' => 'required|numeric|min:1|max:9999',
            'status' => 'required|in:Active,Inactive',
        ]);

        $type = AssessmentType::findOrFail($request->assessment_type_id);

        $nameExists = AssessmentType::where('name', trim($request->name))
            ->where('id', '!=', $type->id)
            ->exists();

        if ($nameExists) {
            return back()->with('message_error', 'This assessment type already exists.');
        }

        $type->name = trim($request->name);
        $type->category = trim($request->category);
        $type->sort_order = (int) $request->sort_order;
        $type->max_number = (int) $request->max_number;
        $type->total_score = round((float) $request->total_score, 2);
        $type->status = trim($request->status);
        $type->updated_by = Auth::id();
        $type->save();

        return back()->with('message_success', 'Assessment type updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'assessment_type_id' => 'required|exists:assessment_types,id',
        ]);

        $type = AssessmentType::query()
            ->withCount('assessments')
            ->findOrFail($request->assessment_type_id);

        if ($type->isInUse()) {
            return back()->with(
                'message_error',
                'This assessment type cannot be deleted because it is already used by '.$type->assessments_count.' assessment(s).'
            );
        }

        $type->delete();

        return back()->with('message_success', 'Assessment type deleted successfully.');
    }

    private function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name, '_');
        $slug = $base !== '' ? $base : 'type';
        $counter = 2;

        while (AssessmentType::where('slug', $slug)->exists()) {
            $slug = $base.'_'.$counter;
            $counter++;
        }

        return $slug;
    }
}

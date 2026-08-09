<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDoc;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class StudentController extends Controller
{
    public function getAddStudentView()
    {
        $studentCode = $this->generateStudentCode();

        return view('student.add-student', [
            'studentCode' => $studentCode,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function addStudent(Request $request)
    {
        $request->validate($this->studentValidationRules());
        $request->validate($this->parentValidationRules());

        if ($request->student_record_id) {
            $student = Student::findOrFail($request->student_record_id);
        } else {
            if (Student::where('student_id', $request->student_id)->where('status', '!=', 'Draft')->count() > 0) {
                return back()->with('message_error', 'Student ID already exists.');
            }
            $student = new Student();
            $student->created_by = Auth::id();
        }

        $this->fillStudentFromRequest($student, $request);
        $student->status = trim($request->status);
        $student->updated_by = Auth::id();
        $student->save();

        $this->saveStudentDocuments($request, $student->id);

        return redirect()->route('list-students')->with('message_success', 'Student registered successfully.');
    }

    public function saveStudentDraft(Request $request)
    {
        $step = (int) $request->current_step;

        if ($step === 1) {
            $request->validate($this->studentValidationRules());
        }

        if ($step === 2) {
            $request->validate($this->parentValidationRules());
        }

        if ($request->student_record_id) {
            $student = Student::findOrFail($request->student_record_id);
            $previousStatus = $student->status;
        } else {
            if (Student::where('student_id', $request->student_id)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID already exists.',
                ], 422);
            }

            $student = new Student();
            $student->created_by = Auth::id();
            $previousStatus = null;
        }

        $this->fillStudentFromRequest($student, $request);

        if ($previousStatus && $previousStatus !== 'Draft') {
            $student->status = $previousStatus;
        } else {
            $student->status = 'Draft';
        }
        $student->updated_by = Auth::id();
        $student->save();

        $this->saveStudentDocuments($request, $student->id);

        return response()->json([
            'success' => true,
            'message' => 'Progress saved. You can continue later.',
            'student_record_id' => $student->id,
        ]);
    }

    public function getListStudentsView()
    {
        $students = Student::orderBy('id', 'desc')->get();

        return view('student.list-students', [
            'students' => $students,
        ]);
    }

    public function getEditStudentView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);
        $docs = StudentDoc::where('student_id', $decodeId)->get();

        return view('student.edit-student', [
            'student' => $student,
            'id' => $id,
            'docs' => $docs,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function updateStudent(Request $request, $id)
    {
        $request->validate($this->studentValidationRules());
        $request->validate($this->parentValidationRules());

        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);

        $idExists = Student::where('student_id', $request->student_id)
            ->where('id', '!=', $decodeId)
            ->count();

        if ($idExists > 0) {
            return back()->with('message_error', 'Student ID already exists.');
        }

        $this->fillStudentFromRequest($student, $request);
        $student->status = trim($request->status);
        $student->updated_by = Auth::id();
        $student->save();

        $this->saveStudentDocuments($request, $student->id);

        return redirect()->route('list-students')->with('message_success', 'Student updated successfully.');
    }

    public function getStudentDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);
        $docs = StudentDoc::where('student_id', $decodeId)->get();

        return view('student.view-student-details', [
            'student' => $student,
            'docs' => $docs,
            'id' => $id,
        ]);
    }

    public function printStudentDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);

        return view('student.print-student-details', $this->buildStudentPrintViewData($student, $id, true));
    }

    public function publicStudentProfileView($id)
    {
        $student = Student::findOrFail($id);
        $encryptedId = Crypt::encrypt($student->id);

        return view('student.print-student-details', $this->buildStudentPrintViewData($student, $encryptedId, false));
    }

    private function buildStudentPrintViewData(Student $student, $encryptedId, $autoPrint)
    {
        $docs = StudentDoc::where('student_id', $student->id)->get();
        $school = SchoolSetting::current();

        return [
            'student' => $student,
            'docs' => $docs,
            'school' => $school,
            'id' => $encryptedId,
            'title' => 'Student Profile',
            'autoPrint' => $autoPrint,
            'isPublicView' => !$autoPrint,
            'profileUrl' => URL::signedRoute('public-student-profile', ['id' => $student->id]),
        ];
    }

    private function generateStudentCode()
    {
        $lastStudent = Student::latest('id')->first();
        $number = $lastStudent ? $lastStudent->id + 1 : 1;

        return 'STD-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    private function studentValidationRules()
    {
        return [
            'student_id' => 'required',
            'academic_year' => 'required',
            'class_name' => 'required',
            'firstname' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'phone' => 'required',
        ];
    }

    private function parentValidationRules()
    {
        return [
            'father_name' => 'required',
            'father_phone' => 'required',
            'mother_name' => 'required',
            'mother_phone' => 'required',
            'guardian_type' => 'required',
            'guardian_name' => 'required',
            'guardian_phone' => 'required',
        ];
    }

    private function fillStudentFromRequest(Student $student, Request $request)
    {
        $student->student_id = trim($request->student_id);
        $student->academic_year = trim($request->academic_year);
        $student->class_name = trim($request->class_name);
        $student->section = $this->cleanInput($request->section) ?? '';
        $student->roll_number = $this->cleanInput($request->roll_number) ?? '';
        $student->firstname = trim($request->firstname);
        $student->othername = $this->cleanInput($request->othername);
        $student->surname = trim($request->surname);
        $student->category = $this->cleanInput($request->category);
        $student->gender = trim($request->gender);
        $student->dob = trim($request->dob);
        $student->phone = trim($request->phone);
        $student->email = $this->cleanInput($request->email);
        $student->status = trim($request->status ?? '') ?: 'Draft';

        $student->father_name = $this->cleanInput($request->father_name);
        $student->father_phone = $this->cleanInput($request->father_phone);
        $student->father_occupation = $this->cleanInput($request->father_occupation);

        $student->mother_name = $this->cleanInput($request->mother_name);
        $student->mother_phone = $this->cleanInput($request->mother_phone);
        $student->mother_occupation = $this->cleanInput($request->mother_occupation);

        $student->guardian_type = $this->cleanInput($request->guardian_type);
        $student->guardian_name = $this->cleanInput($request->guardian_name);
        $student->guardian_email = $this->cleanInput($request->guardian_email);
        $student->guardian_phone = $this->cleanInput($request->guardian_phone);
        $student->guardian_occupation = $this->cleanInput($request->guardian_occupation);
        $student->guardian_address = $this->cleanInput($request->guardian_address);

        $student->blood_group = $this->cleanInput($request->blood_group);
        $student->height = $this->cleanInput($request->height);
        $student->weight = $this->cleanInput($request->weight);

        $student->bank_account_number = $this->cleanInput($request->bank_account_number);
        $student->bank_name = $this->cleanInput($request->bank_name);
        $student->ifsc_code = $this->cleanInput($request->ifsc_code);
        $student->national_id_number = $this->cleanInput($request->national_id_number);

        $student->previous_school_name = $this->cleanInput($request->previous_school_name);
        $student->previous_school_address = $this->cleanInput($request->previous_school_address);
        $student->current_address = $this->cleanInput($request->current_address);
        $student->permanent_address = $this->cleanInput($request->permanent_address);
        $student->hostel = $this->cleanInput($request->hostel);
        $student->room_no = $this->cleanInput($request->room_no);
        $student->notes = $this->cleanInput($request->notes);

        if ($request->hasFile('picture')) {
            $student->picture = $this->uploadPhoto($request->file('picture'), 'uploads/student-photos', $student->picture);
        }

        if ($request->hasFile('father_photo')) {
            $student->father_photo = $this->uploadPhoto($request->file('father_photo'), 'uploads/parent-photos', $student->father_photo);
        }

        if ($request->hasFile('mother_photo')) {
            $student->mother_photo = $this->uploadPhoto($request->file('mother_photo'), 'uploads/parent-photos', $student->mother_photo);
        }

        if ($request->hasFile('guardian_photo')) {
            $student->guardian_photo = $this->uploadPhoto($request->file('guardian_photo'), 'uploads/parent-photos', $student->guardian_photo);
        }
    }

    private function cleanInput($value)
    {
        if ($value === null) {
            return null;
        }

        return trim($value);
    }

    private function uploadPhoto($file, $folder, $oldPath = null)
    {
        if (!empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($folder), $filename);

        return $folder . '/' . $filename;
    }

    private function saveStudentDocuments(Request $request, $studentId)
    {
        if (!$request->has('documents')) {
            return;
        }

        foreach ($request->documents as $index => $doc) {
            $file = $request->file("documents.$index.document");

            if (!$file || empty($doc['doc_name'])) {
                continue;
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/studentdocs'), $filename);

            StudentDoc::create([
                'student_id' => $studentId,
                'doc_name' => $doc['doc_name'],
                'document_path' => 'uploads/studentdocs/' . $filename,
                'created_by' => Auth::id(),
            ]);
        }
    }
}

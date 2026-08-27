<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\HrDeductionType;
use App\Models\HrDepartment;
use App\Models\HrEarningType;
use App\Models\HrLeaveRequest;
use App\Models\HrPayGrade;
use App\Models\HrPayslip;
use App\Models\HrPosition;
use App\Models\HrStaffAttendance;
use App\Models\HrStaffDeduction;
use App\Models\HrStaffEarning;
use App\Models\Staff;
use App\Models\StaffDoc;
use App\Models\User;
use App\Models\UserCat;
use App\Models\UserAccessLink;
use App\Models\UserLink;
use App\Services\Hr\LeaveBalanceService;
use App\Support\TeacherCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function getstaffView()
    {

      // Generate Staff Code
        $lastItem = Staff::latest('id')->first();

        if($lastItem){
            $number = $lastItem->id + 1;
        } else {
            $number = 1;
        }

        $staffCode = 'STAFF-' . str_pad($number, 5, '0', STR_PAD_LEFT);
        $listcountry = Country::all();
        $categories = UserCat::where('status', 'Active')->orderBy('cat_name')->get();
        $screenLinks = $this->getScreenLinks();

        return view('staff.add-staff', array_merge([
            'listcountry' => $listcountry,
            'staffCode' => $staffCode,
            'categories' => $categories,
            'staffUser' => null,
            'assignedExtraLinkIds' => [],
            'savedAccessLinks' => [],
        ], $screenLinks, $this->hrFormData()));
    }
    

    public function addStaff(Request $request)
    {
        $enableLogin = $this->wantsSystemAccess($request);

        $rules = [
            'title' => 'required',
            'surname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required',
            'extra_link_ids' => 'nullable|array',
            'removed_qualification_ids' => 'nullable|array',
            'removed_qualification_ids.*' => 'integer',
        ];

        $rules = array_merge($rules, $this->hrValidationRules(), $this->qualificationValidationRules($request));

        if ($enableLogin) {
            $rules['user_cat'] = 'required';
            $rules['password'] = $this->loginPasswordRule($request);
        }

        $request->validate($rules);


        if(Staff::where('employee_id',$request->staff_number)->get()->count() > 0){

            return back()->with('message_error','Record already exist');

        }else{

        $insertstaff = new Staff();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;

            $file->move(public_path('uploads/profile-photo'), $filename);

            $insertstaff->picture = 'uploads/profile-photo/'.$filename;
        }
        $insertstaff->title = trim($request->title);
        $insertstaff->surname = trim($request->surname);
        $insertstaff->firstname = trim($request->firstname);
        $insertstaff->othername = trim($request->othername);
        $insertstaff->gender = trim($request->gender);
        $insertstaff->email = trim($request->email);
        $insertstaff->mobile = trim($request->phone);
        $insertstaff->employee_id = trim($request->staff_number);
        $insertstaff->residential_address = trim($request->address);
        $insertstaff->status = trim($request->status);
        $insertstaff->nationality = trim($request->nationality);
        $insertstaff->marital_status = trim($request->marital_status);
        $insertstaff->dob = trim($request->dob);
        $insertstaff->created_by = Auth::user()->id;
        $this->applyHrFields($insertstaff, $request);

        $status = $insertstaff->save();

        if (!$status) {
            return back()->with('error_message', 'Something went wrong, please try again.');
        }

        if ($enableLogin) {
            $user = $this->saveStaffUserLogin($insertstaff, $request);
            $this->saveUserAccessLinks($user->id, $request->extra_link_ids ?? []);
        }

        $this->saveStaffQualifications($insertstaff, $request);
        $this->saveStaffCompensation($insertstaff, $request);
        app(LeaveBalanceService::class)->ensureBalances();

        return redirect()->route('list-staff')->with('message_success', 'Staff added successfully');
    }

    }

    public function getListstaffView()
    {
        $liststaff = Staff::where('status','Active')->get();
        return view('staff.list-staff',['liststaff'=>$liststaff]);
    }

    //get Staff details
      public function getStaffID($id)
    {
         $data = Staff::findOrFail($id);
          return response()->json($data);
    }

     //Delete Staff
     public function deleteStaff(Request $request)
    {
        Staff ::where('id',$request->staff_id)->delete();
        return redirect('list-staff')->with('message_success','Staff deleted successfully!');
    }

    
    public function addDocStaff(Request $request)
    {
        foreach($request->documents as $index => $doc)
        {
            $file = $request->file("documents.$index.document");

            if(!$file){
                continue;
            }

            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(
                $this->staffDocsUploadPath(),
                $filename
            );

            StaffDoc::create([
                'staff_id'       => $request->staff_number,
                'level'    => $doc['level'],
                'year'    => $doc['year'],
                'qualification'    => $doc['qualification'],
                'institution'    => $doc['institution'] ?? null,
                'document_path'  => $filename,
                'created_by'     => Auth::id()
            ]);
        }

        return back()->with(
            'message_success',
            'Document saved successfully'
        );
    }

    public function geteditStaffID($id)
    {
        $decodeId = Crypt::decrypt($id);
        $datas = Staff::find($decodeId);
        $listcountry = Country::all();
        $categories = UserCat::where('status', 'Active')->orderBy('cat_name')->get();
        $screenLinks = $this->getScreenLinks();
        $staffUser = User::where('staff_id', $decodeId)->first();

        if (! $staffUser && ! empty($datas->email)) {
            $staffUser = User::where('email', $datas->email)->first();
        }

        $savedAccess = $this->getSavedAccessLinksForUser($staffUser);
        $existingQualifications = StaffDoc::where('staff_id', $decodeId)->orderBy('year')->get();
        $assignedEarnings = HrStaffEarning::where('staff_id', $decodeId)->pluck('amount', 'earning_type_id')->all();
        $assignedDeductions = HrStaffDeduction::where('staff_id', $decodeId)->pluck('amount', 'deduction_type_id')->all();

        return view('staff.edit-staff', array_merge([
            'datas' => $datas,
            'listcountry' => $listcountry,
            'id' => $id,
            'categories' => $categories,
            'staffUser' => $staffUser,
            'assignedExtraLinkIds' => $savedAccess['ids'],
            'savedAccessLinks' => $savedAccess['links'],
            'existingQualifications' => $existingQualifications,
            'assignedEarnings' => $assignedEarnings,
            'assignedDeductions' => $assignedDeductions,
        ], $screenLinks, $this->hrFormData()));
    }

    public function updateStaff(Request $request, $id)
    {

       $decodeId = Crypt::decrypt($id);
       $insertstaff = Staff::findOrFail($decodeId);
       $staffUser = User::where('staff_id', $decodeId)->first();
       $enableLogin = $this->wantsSystemAccess($request);

       $rules = [
            'title' => 'required',
            'surname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required',
            'extra_link_ids' => 'nullable|array',
            'removed_qualification_ids' => 'nullable|array',
            'removed_qualification_ids.*' => 'integer',
        ];

       $rules = array_merge($rules, $this->hrValidationRules(), $this->qualificationValidationRules($request));

       if ($enableLogin) {
           $rules['user_cat'] = 'required';
           $rules['password'] = $this->loginPasswordRule($request, $staffUser, $insertstaff);
       }

       $request->validate($rules);


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;

            // Delete old picture if it exists, before saving the new one
            if (!empty($insertstaff->picture) && file_exists(public_path($insertstaff->picture))) {
                unlink(public_path($insertstaff->picture));
            }

            $file->move(public_path('uploads/profile-photo'), $filename);

            $insertstaff->picture = 'uploads/profile-photo/' . $filename;
        }
        $insertstaff->title = trim($request->title);
        $insertstaff->surname = trim($request->surname);
        $insertstaff->firstname = trim($request->firstname);
        $insertstaff->othername = trim($request->othername);
        $insertstaff->gender = trim($request->gender);
        $insertstaff->email = trim($request->email);
        $insertstaff->mobile = trim($request->phone);
        $insertstaff->employee_id = trim($request->staff_number);
        $insertstaff->residential_address = trim($request->address);
        $insertstaff->status = trim($request->status);
        $insertstaff->nationality = trim($request->nationality);
        $insertstaff->marital_status = trim($request->marital_status);
        $insertstaff->dob = trim($request->dob);
        $insertstaff->created_by = Auth::user()->id;
        $this->applyHrFields($insertstaff, $request);

        $status = $insertstaff->save();

        if (!$status) {
            return back()->with('error_message', 'Something went wrong, please try again.');
        }

        if ($enableLogin) {
            $staffUser = $this->saveStaffUserLogin($insertstaff, $request, $staffUser);
            $this->saveUserAccessLinks($staffUser->id, $request->extra_link_ids ?? []);
        } elseif ($staffUser) {
            UserAccessLink::where('user_id', $staffUser->id)->delete();
            $staffUser->delete();
        }

        $this->saveStaffQualifications($insertstaff, $request);
        $this->saveStaffCompensation($insertstaff, $request);

        return redirect()->route('edit-staff', $id)->with('message_success', 'Staff updated successfully');
    }

    public function getstaffDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $datas = Staff::with(['department', 'hrPosition', 'payGrade', 'country', 'user'])
            ->find($decodeId);
        $listcountry = Country::all();
        $listdoc = StaffDoc::where('staff_id',$decodeId)->get();
        $leaveRequests = HrLeaveRequest::with('leaveType')->where('staff_id', $decodeId)->latest()->get();
        $attendance = HrStaffAttendance::where('staff_id', $decodeId)->orderByDesc('date')->limit(30)->get();
        $payslips = HrPayslip::with('payrollRun')->where('staff_id', $decodeId)->latest()->get();

        return view('staff.view-staff-details',[
            'datas'=>$datas,
            'listcountry'=>$listcountry,
            'id'=>$id,
            'listdoc'=>$listdoc,
            'leaveRequests' => $leaveRequests,
            'attendance' => $attendance,
            'payslips' => $payslips,
        ]);
    }

    public function getstaffprofileView()
    {
        $user = Auth::user();
        $datas = $user?->staff;

        if (! $datas && filled($user?->email)) {
            $datas = Staff::query()->where('email', $user->email)->first();
        }

        $datas?->load(['country', 'hrPosition', 'department']);

        return view('staff.profile', [
            'datas' => $datas,
            'user' => $user,
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();
        $staff = $user?->staff;

        if (! $staff && filled($user?->email)) {
            $staff = Staff::query()->where('email', $user->email)->first();
        }

        $directory = public_path('uploads/profile-photo');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $filename = time().'_'.$user->id.'.'.$file->getClientOriginalExtension();
        $path = 'uploads/profile-photo/'.$filename;

        $oldPath = $staff?->picture ?: $user->photo;
        if ($oldPath && is_file(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        $file->move($directory, $filename);

        $user->photo = $path;
        $user->save();

        if ($staff) {
            $staff->picture = $path;
            $staff->save();
        }

        return back()->with('message_success', 'Photo uploaded successfully');
    }

   //Updating User Password

     public function updatePassword(Request $request){
        session(['active_tab' => $request->active_tab]);
       $request->validate([
              'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ], [
            'confirm_password.same' => 'The password confirmation does not match.',
            'confirm_password.required' => 'Please confirm your password.',
        ]);

         $user = Auth::user();

    // Check if the old password matches
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->with('message_error', 'Current password is incorrect.');
    }

    // Update new password
    $user->update([
        'password' => Hash::make($request->new_password)
    ]);
    
    return redirect()->back()
        ->with('active_tab', $request->active_tab)
        ->with('message_success', 'Password changed successfully..');
    
    }

    private function wantsSystemAccess(Request $request)
    {
        return $request->boolean('enable_system_access');
    }

    private function getScreenLinks()
    {
        $parentLinks = UserLink::where('link_parent', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        $childLinks = UserLink::where('link_parent', '>', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        return compact('parentLinks', 'childLinks');
    }

    private function getSavedAccessLinksForUser(?User $staffUser): array
    {
        if (! $staffUser) {
            return [
                'ids' => [],
                'links' => [],
            ];
        }

        $links = UserAccessLink::query()
            ->where('user_access_links.user_id', $staffUser->id)
            ->join('user_links', 'user_access_links.link_id', '=', 'user_links.link_id')
            ->where('user_links.link_parent', '>', 0)
            ->where('user_links.link_url', '!=', '#')
            ->where('user_links.status', 'Active')
            ->orderBy('user_links.link_name')
            ->get([
                'user_links.link_id',
                'user_links.link_name',
            ]);

        return [
            'ids' => $links->pluck('link_id')->map(fn ($id) => (int) $id)->values()->all(),
            'links' => $links->map(fn ($link) => [
                'link_id' => (int) $link->link_id,
                'link_name' => $link->link_name,
            ])->values()->all(),
        ];
    }

    private function expandLinkIdsWithParents(array $selectedLinkIds)
    {
        $linkIdsToSave = [];

        foreach ($selectedLinkIds as $linkId) {
            $linkIdsToSave[] = (int) $linkId;

            $link = UserLink::find($linkId);

            if ($link && $link->link_parent > 0) {
                $linkIdsToSave[] = $link->link_parent;
            }
        }

        return array_unique($linkIdsToSave);
    }

    private function loginPasswordRule(Request $request, ?User $linkedUser = null, ?Staff $staff = null)
    {
        $existingUser = $this->findExistingLoginUser($request, $linkedUser, $staff);

        return $existingUser ? 'nullable|string|min:8' : 'required|string|min:8';
    }

    private function findExistingLoginUser(Request $request, ?User $linkedUser = null, ?Staff $staff = null)
    {
        $user = User::where('email', trim($request->email))->first();

        if ($user) {
            return $user;
        }

        if ($linkedUser) {
            return $linkedUser;
        }

        if ($staff && $staff->id) {
            return User::where('staff_id', $staff->id)->first();
        }

        return null;
    }

    private function saveStaffUserLogin(Staff $staff, Request $request, ?User $existingUser = null)
    {
        $user = $this->findExistingLoginUser($request, $existingUser, $staff) ?? new User();
        $isNewUser = ! $user->exists;

        if ($isNewUser) {
            $user->password = Hash::make($request->password);
        } elseif ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->name = trim($request->firstname . ' ' . ($request->othername ? $request->othername . ' ' : '') . $request->surname);
        $user->email = trim($request->email);
        $user->phone = trim($request->phone);
        $user->user_cat = $request->user_cat;
        $user->cat_id = $request->user_cat;
        $user->staff_id = $staff->id;
        $user->school_id = $staff->school_id ?? \App\Support\TenantContext::schoolId();
        $user->status = trim($request->status);
        $user->save();

        return $user;
    }

    private function qualificationValidationRules(Request $request): array
    {
        $maxYear = (int) date('Y') + 1;
        $rules = [
            'qualifications' => 'nullable|array',
            'qualifications.*.id' => 'nullable|integer',
            'qualifications.*.level' => 'nullable|string|max:100',
            'qualifications.*.qualification' => 'nullable|string|max:255',
            'qualifications.*.institution' => 'nullable|string|max:255',
            'qualifications.*.year' => 'nullable',
            'qualifications.*.document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:4096',
        ];

        foreach ($request->input('qualifications', []) as $index => $row) {
            $hasFile = $request->hasFile("qualifications.$index.document");
            $hasAny = filled($row['level'] ?? null)
                || filled($row['qualification'] ?? null)
                || filled($row['institution'] ?? null)
                || filled($row['year'] ?? null)
                || $hasFile
                || filled($row['id'] ?? null);

            if (! $hasAny) {
                continue;
            }

            $rules["qualifications.$index.level"] = 'required|string|max:100';
            $rules["qualifications.$index.qualification"] = 'required|string|max:255';
            $rules["qualifications.$index.year"] = 'required|digits:4|integer|min:1950|max:' . $maxYear;
        }

        return $rules;
    }

    private function saveStaffQualifications(Staff $staff, Request $request): void
    {
        $removedIds = array_values(array_filter(array_map('intval', $request->input('removed_qualification_ids', []))));

        if ($removedIds) {
            StaffDoc::where('staff_id', $staff->id)
                ->whereIn('id', $removedIds)
                ->get()
                ->each(function (StaffDoc $doc) {
                    $this->deleteStaffQualificationFile($doc);
                    $doc->delete();
                });
        }

        foreach ($request->input('qualifications', []) as $index => $row) {
            $level = trim((string) ($row['level'] ?? ''));
            $qualification = trim((string) ($row['qualification'] ?? ''));
            $institution = trim((string) ($row['institution'] ?? ''));
            $year = trim((string) ($row['year'] ?? ''));
            $existingId = $row['id'] ?? null;
            $file = $request->file("qualifications.$index.document");

            if ($level === '' && $qualification === '' && $institution === '' && $year === '' && ! $file) {
                continue;
            }

            $doc = null;
            if ($existingId) {
                $doc = StaffDoc::where('staff_id', $staff->id)->where('id', $existingId)->first();
            }

            if (! $doc) {
                $doc = new StaffDoc();
                $doc->staff_id = $staff->id;
                $doc->created_by = Auth::id();
            }

            if ($file) {
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $index . '_' . $safeName;
                $file->move($this->staffDocsUploadPath(), $filename);
                $this->deleteStaffQualificationFile($doc);
                $doc->document_path = $filename;
            }

            $doc->level = $level;
            $doc->qualification = $qualification;
            $doc->institution = $institution !== '' ? $institution : null;
            $doc->year = $year;
            $doc->save();
        }
    }

    private function staffDocsUploadPath(): string
    {
        $directory = public_path('uploads/staffdocs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory;
    }

    private function deleteStaffQualificationFile(StaffDoc $doc): void
    {
        if (empty($doc->document_path)) {
            return;
        }

        $path = public_path('uploads/staffdocs/' . $doc->document_path);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function hrValidationRules(): array
    {
        $hasPositions = HrPosition::where('status', 'Active')->exists();

        return [
            'department_id' => 'nullable|exists:hr_departments,id',
            'position_id' => $hasPositions ? 'required|exists:hr_positions,id' : 'nullable',
            'position' => $hasPositions ? 'nullable|string|max:120' : 'required|string|max:120',
            'employment_type' => 'nullable|string|max:40',
            'appointment_date' => 'nullable|date',
            'confirmation_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'ssnit_number' => 'nullable|string|max:40',
            'tin' => 'nullable|string|max:40',
            'bank_name' => 'nullable|string|max:120',
            'bank_branch' => 'nullable|string|max:120',
            'account_name' => 'nullable|string|max:120',
            'account_number' => 'nullable|string|max:40',
            'next_of_kin_name' => 'nullable|string|max:120',
            'next_of_kin_phone' => 'nullable|string|max:40',
            'next_of_kin_relationship' => 'nullable|string|max:40',
            'pay_grade_id' => 'nullable|exists:hr_pay_grades,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'earnings' => 'nullable|array',
            'deductions' => 'nullable|array',
        ];
    }

    private function hrFormData(): array
    {
        return [
            'departments' => HrDepartment::where('status', 'Active')->orderBy('name')->get(),
            'positions' => HrPosition::with('department')->where('status', 'Active')->orderBy('name')->get(),
            'payGrades' => HrPayGrade::where('status', 'Active')->orderBy('name')->get(),
            'earningTypes' => HrEarningType::where('status', 'Active')->orderBy('name')->get(),
            'deductionTypes' => HrDeductionType::where('status', 'Active')->orderBy('name')->get(),
            'teacherPositionId' => HrPosition::query()->where('name', 'Teacher')->value('id'),
            'teacherCategoryId' => TeacherCategory::id(),
        ];
    }

    private function applyHrFields(Staff $staff, Request $request): void
    {
        $position = null;
        if ((int) $request->input('user_cat') === TeacherCategory::id()) {
            $position = HrPosition::teacher();
        } elseif ($request->filled('position_id')) {
            $position = HrPosition::find($request->position_id);
        }

        if ($position) {
            $staff->position_id = $position->id;
            $staff->position = $position->name;
            if ($position->department_id && ! $request->filled('department_id')) {
                $staff->department_id = $position->department_id;
            }
        } else {
            $staff->position_id = null;
            $staff->position = trim((string) $request->position);
        }

        $staff->department_id = $request->department_id ?: ($staff->department_id ?? null);
        $staff->pay_grade_id = $request->pay_grade_id ?: null;
        $staff->basic_salary = $request->filled('basic_salary') ? $request->basic_salary : null;
        $staff->employment_type = $request->employment_type ?: null;
        $staff->appointment_date = $request->appointment_date ?: null;
        $staff->confirmation_date = $request->confirmation_date ?: null;
        $staff->contract_end_date = $request->contract_end_date ?: null;
        $staff->ssnit_number = trim((string) $request->ssnit_number) ?: null;
        $staff->tin = trim((string) $request->tin) ?: null;
        $staff->bank_name = trim((string) $request->bank_name) ?: null;
        $staff->bank_branch = trim((string) $request->bank_branch) ?: null;
        $staff->account_name = trim((string) $request->account_name) ?: null;
        $staff->account_number = trim((string) $request->account_number) ?: null;
        $staff->next_of_kin_name = trim((string) $request->next_of_kin_name) ?: null;
        $staff->next_of_kin_phone = trim((string) $request->next_of_kin_phone) ?: null;
        $staff->next_of_kin_relationship = trim((string) $request->next_of_kin_relationship) ?: null;
    }

    private function saveStaffCompensation(Staff $staff, Request $request): void
    {
        $staff->staffEarnings()->delete();
        foreach ($request->input('earnings', []) as $typeId => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }

            HrStaffEarning::create([
                'staff_id' => $staff->id,
                'earning_type_id' => $typeId,
                'amount' => $amount,
            ]);
        }

        $staff->staffDeductions()->delete();
        foreach ($request->input('deductions', []) as $typeId => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }

            HrStaffDeduction::create([
                'staff_id' => $staff->id,
                'deduction_type_id' => $typeId,
                'amount' => $amount,
            ]);
        }
    }

    private function saveUserAccessLinks($userId, array $selectedLinkIds)
    {
        UserAccessLink::where('user_id', $userId)->delete();

        $selectedLinkIds = array_values(array_unique(array_map('intval', $selectedLinkIds)));
        $linkIdsToSave = $this->expandLinkIdsWithParents($selectedLinkIds);

        foreach ($linkIdsToSave as $linkId) {
            UserAccessLink::create([
                'user_id' => $userId,
                'link_id' => $linkId,
            ]);
        }
    }

    
}

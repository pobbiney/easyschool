<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Staff;
use App\Models\StaffDoc;
use App\Models\User;
use App\Models\UserCat;
use App\Models\UserAccessLink;
use App\Models\UserLink;
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
        ], $screenLinks));
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
            'position' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required',
            'extra_link_ids' => 'nullable|array',
        ];

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
        $insertstaff->position = trim($request->position);
        $insertstaff->employee_id = trim($request->staff_number);
        $insertstaff->residential_address = trim($request->address);
        $insertstaff->status = trim($request->status);
        $insertstaff->nationality = trim($request->nationality);
        $insertstaff->marital_status = trim($request->marital_status);
        $insertstaff->dob = trim($request->dob);
        $insertstaff->created_by = Auth::user()->id;

        $status = $insertstaff->save();

        if (!$status) {
            return back()->with('error_message', 'Something went wrong, please try again.');
        }

        if ($enableLogin) {
            $user = $this->saveStaffUserLogin($insertstaff, $request);
            $this->saveUserAccessLinks($user->id, $request->extra_link_ids ?? []);
        }

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
                public_path('uploads/staffdocs'),
                $filename
            );

            StaffDoc::create([
                'staff_id'       => $request->staff_number,
                'level'    => $doc['level'],
               
                'year'    => $doc['year'],
                'qualification'    => $doc['qualification'],
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

        return view('staff.edit-staff', array_merge([
            'datas' => $datas,
            'listcountry' => $listcountry,
            'id' => $id,
            'categories' => $categories,
            'staffUser' => $staffUser,
            'assignedExtraLinkIds' => $savedAccess['ids'],
            'savedAccessLinks' => $savedAccess['links'],
        ], $screenLinks));
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
            'position' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required',
            'extra_link_ids' => 'nullable|array',
        ];

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
        $insertstaff->position = trim($request->position);
        $insertstaff->employee_id = trim($request->staff_number);
        $insertstaff->residential_address = trim($request->address);
        $insertstaff->status = trim($request->status);
        $insertstaff->nationality = trim($request->nationality);
        $insertstaff->marital_status = trim($request->marital_status);
        $insertstaff->dob = trim($request->dob);
        $insertstaff->created_by = Auth::user()->id;

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

        return redirect()->route('edit-staff', $id)->with('message_success', 'Staff updated successfully');
    }

    public function getstaffDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $datas = Staff::find($decodeId);
        $listcountry = Country::all();
        $listdoc = StaffDoc::where('staff_id',$decodeId)->get();
        return view('staff.view-staff-details',['datas'=>$datas,'listcountry'=>$listcountry,'id'=>$id,'listdoc'=>$listdoc]);
    }

    public function getstaffprofileView()
    {

        $datas = Staff::where('id', Auth::user()->staff_id)->first();
        return view('staff.profile',['datas'=>$datas]);
    }

    public function updatePhoto(Request $request)
{
    $request->validate([
        'staff_id' => 'required',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $insertstaff = Staff::find($request->staff_id);

    if (!$insertstaff) {
        return back()->with('message_error', 'Staff record not found.');
    }

    $file = $request->file('image');
    $ext = $file->getClientOriginalExtension();
    $filename = time() . '.' . $ext;

    // Delete old picture if it exists, before saving the new one
    if (!empty($insertstaff->picture) && file_exists(public_path($insertstaff->picture))) {
        unlink(public_path($insertstaff->picture));
    }

    $file->move(public_path('uploads/profile-photo'), $filename);

    $insertstaff->picture = 'uploads/profile-photo/' . $filename;
    $status = $insertstaff->save();

    return $status
        ? back()->with('message_success', 'Photo uploaded successfully')
        : back()->with('message_error', 'Something went wrong, please try again.');
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
        return back()->with('error_message', 'Current password is incorrect.');
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
        $user->status = trim($request->status);
        $user->save();

        return $user;
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

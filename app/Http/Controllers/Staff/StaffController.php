<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Staff;
use App\Models\StaffDoc;
use App\Models\User;
use App\Models\UserCat;
use App\Models\UserCatLink;
use App\Models\UserExtraLink;
use App\Models\UserLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
            'email' => $enableLogin ? 'required|email|unique:users,email' : 'required|email',
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
            $rules['password'] = 'required|string|min:8';
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
            $user = new User();
            $user->name = trim($request->firstname . ' ' . ($request->othername ? $request->othername . ' ' : '') . $request->surname);
            $user->email = trim($request->email);
            $user->phone = trim($request->phone);
            $user->password = Hash::make($request->password);
            $user->user_cat = $request->user_cat;
            $user->cat_id = $request->user_cat;
            $user->staff_id = $insertstaff->id;
            $user->status = trim($request->status);
            $user->save();

            $this->saveUserExtraLinks($user->id, $request->extra_link_ids ?? [], $request->user_cat);
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
        $assignedExtraLinkIds = [];

        if ($staffUser) {
            $assignedExtraLinkIds = UserExtraLink::where('user_id', $staffUser->id)
                ->pluck('link_id')
                ->toArray();
        }

        return view('staff.edit-staff', array_merge([
            'datas' => $datas,
            'listcountry' => $listcountry,
            'id' => $id,
            'categories' => $categories,
            'staffUser' => $staffUser,
            'assignedExtraLinkIds' => $assignedExtraLinkIds,
        ], $screenLinks));
    }

    public function updateStaff(Request $request, $id)
    {

       $decodeId = Crypt::decrypt($id);
       $staffUser = User::where('staff_id', $decodeId)->first();
       $enableLogin = $this->wantsSystemAccess($request);

       $emailRule = 'required|email';
       if ($enableLogin) {
           $emailRule = 'required|email|unique:users,email';
           if ($staffUser) {
               $emailRule .= ',' . $staffUser->id;
           }
       }

       $rules = [
            'title' => 'required',
            'surname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'email' => $emailRule,
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
           $rules['password'] = $staffUser ? 'nullable|string|min:8' : 'required|string|min:8';
       }

       $request->validate($rules);


        $insertstaff = Staff::findOrFail($decodeId);

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
            if (!$staffUser) {
                $staffUser = new User();
                $staffUser->password = Hash::make($request->password);
            } elseif ($request->filled('password')) {
                $staffUser->password = Hash::make($request->password);
            }

            $staffUser->name = trim($request->firstname . ' ' . ($request->othername ? $request->othername . ' ' : '') . $request->surname);
            $staffUser->email = trim($request->email);
            $staffUser->phone = trim($request->phone);
            $staffUser->user_cat = $request->user_cat;
            $staffUser->cat_id = $request->user_cat;
            $staffUser->staff_id = $insertstaff->id;
            $staffUser->status = trim($request->status);
            $staffUser->save();

            $this->saveUserExtraLinks($staffUser->id, $request->extra_link_ids ?? [], $request->user_cat);
        } elseif ($staffUser) {
            UserExtraLink::where('user_id', $staffUser->id)->delete();
            $staffUser->delete();
        }

        return redirect()->route('list-staff')->with('message_success', 'Staff updated successfully');
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

    private function saveUserExtraLinks($userId, array $selectedLinkIds, $categoryId = null)
    {
        UserExtraLink::where('user_id', $userId)->delete();

        if ($categoryId) {
            $categoryLinkIds = UserCatLink::where('cat_id', $categoryId)
                ->pluck('link_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $selectedLinkIds = array_values(array_filter($selectedLinkIds, function ($linkId) use ($categoryLinkIds) {
                return !in_array((int) $linkId, $categoryLinkIds, true);
            }));
        }

        $linkIdsToSave = $this->expandLinkIdsWithParents($selectedLinkIds);

        foreach ($linkIdsToSave as $linkId) {
            UserExtraLink::create([
                'user_id' => $userId,
                'link_id' => $linkId,
            ]);
        }
    }

    
}

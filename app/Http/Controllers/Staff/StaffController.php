<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Staff;
use App\Models\StaffDoc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PhpParser\Builder\Function_;

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
        return view('staff.add-staff',['listcountry'=>$listcountry,'staffCode'=>$staffCode]);
    }
    

    public function addStaff(Request $request)
    {
         $request->validate([
            'title' => 'required',
            'surname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'phone' => 'required',
             
            'dob' => 'required',
            'position' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required'
        ]);


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

        return $status 
            ? redirect()->route('list-staff')->with('message_success','Staff added successfully') 
            : back()->with('error_message','Something went wrong, please try again.');
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
        return view('staff.edit-staff',['datas'=>$datas,'listcountry'=>$listcountry,'id'=>$id]);
    }

    public function updateStaff(Request $request, $id)
    {

       $decodeId = Crypt::decrypt($id);
         $request->validate([
            'title' => 'required',
            'surname' => 'required',
            'firstname' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'phone' => 'required',
             
            'dob' => 'required',
            'position' => 'required',
            'nationality' => 'required',
            'marital_status'=> 'required',
            'address'=> 'required',
            'status' => 'required'
        ]);


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

        return $status 
            ? redirect()->route('list-staff')->with('message_success','Staff updated successfully') 
            : back()->with('error_message','Something went wrong, please try again.');
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

    
}

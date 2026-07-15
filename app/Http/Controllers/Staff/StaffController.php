<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
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
            ? back()->with('message_success','Staff added successfully') 
            : back()->with('error_message','Something went wrong, please try again.');
    }

    }
}

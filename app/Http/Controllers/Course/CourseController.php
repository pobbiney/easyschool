<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function getAddCourseView()
    {
        $listcourse = Course::where('status','Active')->get();
        return view('course-setup.add-course',['listcourse'=>$listcourse]);
    }

    public function addCourse(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
            'course_category' => 'required',
            
        ]);

         if(Course::where('name',$request->name)->get()->count() > 0){

            return back()->with('message_error','Course has been added  already');

        }else{
            $insertCat = new Course();
            $insertCat->name = trim($request->name);
            $insertCat->description = trim($request->description);
            $insertCat->category = trim($request->course_category);
            $insertCat->status = trim($request->status);
            $insertCat->created_by       = Auth::id();
            $insertCat = $insertCat->save();
            return $insertCat ? back()->with('message_success','Course   added successfully') : back()->with('message_error','Something went wrong, please try again.');
    }
    }

    //get Staff details
      public function getCourseID($id)
    {
         $data = Course::findOrFail($id);
          return response()->json($data);
    }

    public function updateCourse(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
            'course_category' => 'required',
            
        ]);
            $insertCat = Course::find($request->course_id);
            $insertCat->name = trim($request->name);
            $insertCat->description = trim($request->description);
            $insertCat->category = trim($request->course_category);
            $insertCat->status = trim($request->status);
           
            $insertCat = $insertCat->save();
            return $insertCat ? back()->with('message_success','Course   updated successfully') : back()->with('message_error','Something went wrong, please try again.');
    }
}

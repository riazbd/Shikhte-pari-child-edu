<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseContent;
use App\Models\CoursePurchase;
use App\Models\CourseAssign;
use App\Models\CourseEnrollment;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = [];
        $data = Course::with('category')->with('instructor');
        if ($request->category && $request->category != '') {
            $data = $data->whereHas('category', function (Builder $query) use ($request) {
                $query->where('name', $request->category);
            });
        }
        if ($request->search && $request->search != '') {
            $data = $data->where('name', 'like', '%' . $request->search . '%');
        }
        $courses = $data->get();
        return $courses;
    }

    public function createdCourses()
    {
        $courses = Course::where('user_id', Auth::id())->get();
        return response()->json([
            'success' => false,
            'courses' => $courses
        ], 200);
    }

    public function courseDetails(Request $request)
    {
        $course = Course::where('id', $request->course_id)->with('instructor')->with('contents')->first();
        return response()->json([
            'success' => false,
            'course' => $course
        ], 200);
    }
    public function courseCreate(Request $request)
    {

        if (Auth::user()->hasPermissionTo('course-create', 'api')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'category' => 'required|string',
                // 'email' => 'required|string|unique:users',
                'price' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages()->toArray()
                ], 500);
            }

            $file = $request->file('image');
            $destination = public_path("/public/uploads");
            $file->move($destination, $file->getClientOriginalName());

            $course = new Course();
            // $course->id = Str::uuid()->toString();
            $course->user_id = Auth::id();
            $course->name = $request->name;
            $course->image = $file->getClientOriginalName();
            $course->description = $request->description;
            $course->category_id = $request->category;
            // $course->category = ;
            $course->tags = $request->tags || "default tags";
            $course->price = $request->price;
            $course->save();
            $course->contents=[];

            return response()->json([
                'success' => true,
                'message' => "Course created successfully!",
                'course' => $course
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }
    public function courseUpdate(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-edit', 'api')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'category' => 'required|string',
                // 'email' => 'required|string|unique:users',
                'price' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages()->toArray()
                ], 500);
            }

            $course = Course::find($request->course_id);
            if ($course && $course->user_id == Auth::id()) {
                $course->name = $request->name;
                $course->description = $request->description;
                $course->category_id = $request->category_id;
                $course->tags = $request->tags;
                $course->price = $request->price;
                $course->save();
                return response()->json([
                    'success' => true,
                    'message' => "Course: " . $course->name . " updated successfully!",
                    'course' => $course
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Course not found!"
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function courseDelete(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-delete', 'api')) {
            $course = Course::find($request->course_id);
            $course->delete();
            return response()->json([
                'success' => true,
                'message' => "Course: " . $course->name . " deleted successfully!"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function courseContentCreate(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-edit', 'api')) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages()->toArray()
                ], 500);
            }
            $course = Course::find($request->course_id);

            if ($course) {
                $courseContent = new CourseContent();
                $courseContent->course_id = $course->id;
                $courseContent->type = $request->type || "video";
                $courseContent->url = $request->url;
                $courseContent->name = $request->name;
                $courseContent->description = $request->description;
                $courseContent->save();

                return response()->json([
                    'success' => true,
                    'message' => "New course module " . $courseContent->name . " added to the course: " . $course->name . " successfully!",
                    'course' => $courseContent
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid course id!"
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function courseContentEdit(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-edit', 'api')) {

            $module = CourseContent::find($request->module_id);

            if ($module && $this->CourseOwnershipCheck($module->course_id, Auth::id())) {

                $module->type = $request->type || "video";
                $module->url = $request->url;
                $module->name = $request->name;
                $module->description = $request->description;
                $module->save();

                return response()->json([
                    'success' => true,
                    'message' => "Module " . $module->name . " updated successfully!",
                    'course' => $module
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Module not found!"
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function courseContentDelete(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-edit', 'api')) {

            $module = CourseContent::find($request->module_id);

            if ($module && $this->CourseOwnershipCheck($module->course_id, Auth::id())) {

                $module->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Module " . $module->name . " deleted successfully!",
                    'course' => $module
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Module not found!"
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function coursePurchase(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-buy', 'api')) {

            $course = Course::find($request->course_id);

            if ($course) {
                $purchase = new CoursePurchase();
                $purchase->user_id = Auth::id();
                $purchase->course_id = $course->id;
                $purchase->save();

                Wishlist::where('user_id', Auth::id())->where('course_id', $request->course_id)->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Course: " . $course->name . " purchase successful!"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid course id!"
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Permission not granted!"
            ], 403);
        }
    }

    public function courseAssign(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-buy', 'api')) {

            if ($this->CoursePurchaseCheck($request->course_id, Auth::id())) {
                foreach ($request->ids as $key => $studentId) {
                    $assigned = CourseAssign::where('user_id', $studentId)->where('course_id', $request->course_id)->first();
                    if (!$assigned) {
                        $course = Course::find($request->course_id);
                        if ($course) {
                            $courseAssign = new CourseAssign();
                            $courseAssign->user_id = $studentId;
                            $courseAssign->course_id = $request->course_id;
                            $courseAssign->assigned_by = Auth::id();
                            $courseAssign->save();
                        }
                    }
                }
            } else {
                return response()->json([
                    'success' => true,
                    'message' => "You need to buy this course first!",
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => "Course assigned successfully!",
            ], 200);
        }
    }

    public function courseAssignInfo( Request $request ){
        $info = CourseAssign::where('assigned_by',Auth::id())->where('course_id', $request->course_id)->get();
        return response()->json([
            'success' => true,
            'info' => $info,
        ], 200);
    }

    public function courseEnroll(Request $request)
    {
        if (Auth::user()->hasPermissionTo('course-enroll', 'api')) {
            $assigned = CourseAssign::where('user_id', Auth::id())->where('course_id', $request->course_id)->first();
            if ($assigned) {
                $enrolled = CourseEnrollment::where('user_id', Auth::id())->where('course_id', $request->course_id)->first();
                if (!$enrolled) {
                    $course = Course::find($request->course_id);
                    $enroll = new CourseEnrollment();
                    $enroll->user_id = Auth::id();
                    $enroll->course_id = $course->id;
                    $enroll->save();
                    return response()->json([
                        'success' => true,
                        'message' => "You have been enrolled to the course: " . $course->name . " successfully!",
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => "You haven already enrolled this course!",
                    ], 401);
                }
            } else {
                return response()->json([
                    'success' => true,
                    'message' => "You haven't been assigned to this course yet!",
                ], 401);
            }
        }
    }

    public function QuizCreate(Request $request)
    {
        $quiz = new Quiz();
        return $quiz;
    }

    private function CourseOwnershipCheck($courseId, $userId)
    {
        $course = Course::find($courseId);
        if ($course && $course->user_id == $userId) {
            return true;
        }
        return false;
    }

    private function CoursePurchaseCheck($courseId, $userId)
    {
        $found = CoursePurchase::where('course_id', $courseId)->where('user_id', $userId)->first();
        if ($found) {
            return true;
        }
        return false;
    }
}

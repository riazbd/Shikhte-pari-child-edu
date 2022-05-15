<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\User;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use App\Models\StudentDetail;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $data = User::orderBy('id', 'DESC')->paginate(5);
        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function userIndex(Request $request)
    {
        $data = User::orderBy('id', 'DESC')->with('roles')->get();
        return $data;
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        foreach ($request->input('roles') as $key => $roleName) {
            $role = Role::where('name', $roleName)->first();
            $user->assignRole($role);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function register(Request $request)
    {
        if ($request->type === 'guardian') {
            return $this->createGuardian($request);
        } else if ($request->type === 'instructor') {
            return $this->createInstructor($request);
        } else if ($request->type === 'facilitator') {
            $role = Role::where('name', 'facilitator')->first();
            // $user->assignRole($role);
        }
    }

    private function createGuardian(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->toArray()
            ], 500);
        }

        $data = [
            "name" => $request->name,
            "username" => $request->username,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ];

        $user = User::create($data);
        $role = Role::where('name', 'guardian')->first();
        $user->assignRole($role);

        $responseMessage = "Registration Successful";
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

    private function createInstructor(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->toArray()
            ], 500);
        }

        $data = [
            "name" => $request->name,
            "username" => $request->username,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ];

        $user = User::create($data);
        $role = Role::where('name', 'instructor')->first();
        $user->assignRole($role);

        $responseMessage = "Registration Successful";
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

    public function createStudent(Request $request)
    {
        if (Auth::user()->hasPermissionTo('child-create', 'api')) {
            if (User::where('parent_id', Auth::id())->count() < 4) {

                $validator = Validator::make($request->all(), [
                    'name' => 'required|string',
                    'username' => 'required|string',
                    // 'email' => 'required|string|unique:users',
                    'password' => 'required|min:6',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->messages()->toArray()
                    ], 500);
                }
                $data = [
                    "name" => $request->name,
                    "username" => $request->username,
                    "email" => rand(),
                    "password" => Hash::make($request->password),
                    "parent_id" => Auth::id(),
                ];
                $user = User::create($data);

                $detail = new StudentDetail();
                $detail->school = $request->school ?? null;
                $detail->dob = $request->dob ?? null;
                $detail->class = $request->class ?? null;
                $detail->gender = $request->gender;
                $detail->user_id = $user->id;
                $detail->save();

                $role = Role::where('name', 'student')->first();
                $user->assignRole($role);
                $responseMessage = "Student Registration Successful";
                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'student' => $user,
                    'detail' => $detail,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum number of students created!'
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Permission error!'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->toArray()
            ], 500);
        }
        $credentials = $request->only(["username", "password"]);
        $user = null;

        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if ($fieldType == 'email') {
            $user = User::where('email', $credentials['username'])->first();
        } else {
            $user = User::where('username', $credentials['username'])->first();
        }

        if ($user) {
            if (!auth()->attempt(array($fieldType => $credentials['username'], 'password' => $credentials['password']))) {
                $responseMessage = "Invalid username or password";
                return response()->json([
                    "success" => false,
                    "message" => $responseMessage,
                    "error" => $responseMessage
                ], 422);
            }
            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            $responseMessage = "Login Successful";
            return response()->json(['token' => $accessToken, 'message' => $responseMessage]);
        } else {
            $responseMessage = "Invalid username or passwords";
            return response()->json([
                "success" => false,
                "message" => $responseMessage,
                "error" => $responseMessage
            ], 422);
        }
    }

    public function viewProfile()
    {
        $responseMessage = "user profile";
        $data = User::where('id', Auth::id())->with('roles')->with('wishlist')->with('wishlist.course')->with('purchases')->first();
        $child = User::where('parent_id', Auth::id())->select(['name', 'id'])->get();
        $data->child = $child;
        return response()->json([
            "success" => true,
            "message" => $responseMessage,
            "data" => $data,
            // "child"=>$child
        ], 200);
    }

    public function getDashboard()
    {

        if (Auth::user()->hasRole('guardian')) {
            return $this->guardianDashItems();
        }
    }
    public function guardianDashItems()
    {
        $child = User::where('parent_id', Auth::id())->select(['name', 'id', 'username', 'email'])->with('studentDetails')->get();
        $courses = CoursePurchase::where('user_id', Auth::id())->with('course')->get();
        return response()->json([
            "success" => true,
            "child" => $child,
            "courses" => $courses,
        ], 200);
    }

    public function logout()
    {
        $user = Auth::guard("api")->user()->token();
        $user->revoke();
        $responseMessage = "successfully logged out";
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

    public function wishlist(Request $request)
    {
        $list = Wishlist::where('user_id', Auth::id())->get();
        return $list;
    }

    public function childWishlist(Request $request)
    {
        if (Auth::user()->hasPermissionTo('wishlist-view', 'api')) {
            $child = User::find($request->user_id);
            if ($child && $child->parent_id == Auth::id()) {
                $list = Wishlist::where('user_id', $request->user_id)->with('course')->get();
                return response()->json([
                    'success' => true,
                    'Student' => $child,
                    'Wishlist' => $list,
                ], 200);
            }
        }
    }

    public function addToWishlist(Request $request)
    {
        $found = Wishlist::where('user_id', Auth::id())->where('course_id', $request->course_id)->first();
        if (!$found) {
            $item = new Wishlist();
            $item->user_id = Auth::id();
            $item->course_id = $request->course_id;
            $item->save();
            return response()->json([
                'success' => true,
                'message' => "Course added to wishlist successfully!",
                'item'=>$item,
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => "This course is already in your wishlist!",
            ], 200);
        }

        return true;
    }

    public function removeFromWishlist(Request $request)
    {
        Wishlist::where('user_id', Auth::id())->where('course_id', $request->course_id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Course removed from wishlist!",
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        return view('home');
    }

    public function categories()
    {
        $category = Category::all();
        return $category;
    }

    public function categoryStore(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->save();
        return $category;
    }

    public function categoryUpdate(Request $request)
    {
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->save();
        return $category;
    }

    public function categoryDelete(Request $request)
    {
        $category = Category::find($request->id);
        $category->delete();
        return true;
    }
}

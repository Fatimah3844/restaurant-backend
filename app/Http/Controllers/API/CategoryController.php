<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index(Request $request)
    {
        
        $cats = Category::orderBy('id','desc')->paginate(10);
        return response()->json($cats);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);
         // check if added category is exist
    $exists = Category::where('name', $request->name)->first();

    if ($exists) {
        return response()->json([
            'message' => 'Category name already exists'
        ], 422);
    }

        $category = Category::create($data);

        return response()->json([
            'message' => 'Category created',
            'data' => $category
        ], 201);
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);
        

        $category->update($data);

        return response()->json([
            'message' => 'Category updated',
            'data' => $category
        ]);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

       
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
}

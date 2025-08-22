<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /api/products
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    // GET /api/products/{id}
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    // POST /api/products
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Check for duplicate product name in same category
        $exists = Product::where('name', $request->name)
                         ->where('category_id', $request->category_id)
                         ->first();
        if ($exists) {
            return response()->json([
                'message' => 'Product with this name already exists in this category'
            ], 422);
        }

        $product = Product::create($request->all());
        return response()->json([
            'message' => 'Product created',
            'data' => $product
        ], 201);
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        // Check duplicate if name/category_id changed
        if ($request->has('name') || $request->has('category_id')) {
            $name = $request->name ?? $product->name;
            $cat_id = $request->category_id ?? $product->category_id;
            $exists = Product::where('name', $name)
                             ->where('category_id', $cat_id)
                             ->where('id', '!=', $id)
                             ->first();
            if ($exists) {
                return response()->json([
                    'message' => 'Product with this name already exists in this category'
                ], 422);
            }
        }

        $product->update($request->all());
        return response()->json([
            'message' => 'Product updated',
            'data' => $product
        ]);
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }
}

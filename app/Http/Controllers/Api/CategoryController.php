<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
  
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['parent', 'children', 'posts']);


        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }


        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }


        if ($request->has('root')) {
            $query->whereNull('parent_id');
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }


        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }


        $orderBy = $request->get('order_by', 'sort_order');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

 
        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

   

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

       
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'data' => $category->load(['parent', 'children']),
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'posts', 'allChildren']);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category retrieved successfully'
        ]);
    }

   


    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

       
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'data' => $category->load(['parent', 'children']),
            'message' => 'Category updated successfully'
        ]);
    }

   
    

    public function destroy(Category $category): JsonResponse
    {
        
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with children'
            ], 422);
        }

        
        if ($category->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with posts'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    
    

    public function posts(Category $category, Request $request): JsonResponse
    {
        $query = $category->posts()->with(['author', 'tags']);


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Category posts retrieved successfully'
        ]);
    }

  


    public function children(Category $category): JsonResponse
    {
        $children = $category->children()->with(['children', 'posts'])->get();

        return response()->json([
            'success' => true,
            'data' => $children,
            'message' => 'Category children retrieved successfully'
        ]);
    }

   
    
    
    public function breadcrumb(Category $category): JsonResponse
    {
        $breadcrumb = $category->getBreadcrumb();

        return response()->json([
            'success' => true,
            'data' => $breadcrumb,
            'message' => 'Category breadcrumb retrieved successfully'
        ]);
    }
}

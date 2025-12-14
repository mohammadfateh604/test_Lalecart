<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    
    
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['author', 'category', 'tags', 'comments']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        if ($request->has('visibility')) {
            $query->where('visibility', $request->visibility);
        } else {
            $query->public();
        }

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('author_id')) {
            $query->byAuthor($request->author_id);
        }

        if ($request->has('tag_id')) {
            $query->byTag($request->tag_id);
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('sticky')) {
            $query->sticky();
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('popular')) {
            $days = $request->get('days', 30);
            $query->popular($days);
        }

        if ($request->has('recent')) {
            $query->recent();
        }


        $orderBy = $request->get('order_by', 'published_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);


        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Posts retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|array',
            'status' => 'required|in:draft,published,archived',
            'visibility' => 'required|in:public,private,password_protected',
            'password' => 'required_if:visibility,password_protected|string',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
            'is_sticky' => 'boolean',
            'published_at' => 'nullable|date',
            'author_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();


        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('posts', 'public');
            $data['featured_image'] = $imagePath;
        }

        $post = Post::create($data);


        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        return response()->json([
            'success' => true,
            'data' => $post->load(['author', 'category', 'tags']),
            'message' => 'Post created successfully'
        ], 201);
    }


    

    public function show(Post $post): JsonResponse
    {
        // Increment view count
        $post->incrementViewCount();

        $post->load(['author', 'category', 'tags', 'comments.user', 'approvedComments.user']);

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|array',
            'status' => 'sometimes|required|in:draft,published,archived',
            'visibility' => 'sometimes|required|in:public,private,password_protected',
            'password' => 'required_if:visibility,password_protected|string',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
            'is_sticky' => 'boolean',
            'published_at' => 'nullable|date',
            'author_id' => 'sometimes|required|exists:users,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('posts', 'public');
            $data['featured_image'] = $imagePath;
        }

        $post->update($data);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json([
            'success' => true,
            'data' => $post->load(['author', 'category', 'tags']),
            'message' => 'Post updated successfully'
        ]);
    }


    

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }


    
     
    public function publish(Post $post): JsonResponse
    {
        $post->publish();

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post published successfully'
        ]);
    }

 
    

    public function unpublish(Post $post): JsonResponse
    {
        $post->unpublish();

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post unpublished successfully'
        ]);
    }

    
    

    public function archive(Post $post): JsonResponse
    {
        $post->archive();

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post archived successfully'
        ]);
    }

  
    

    public function related(Post $post, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $relatedPosts = $post->getRelatedPosts($limit);

        return response()->json([
            'success' => true,
            'data' => $relatedPosts,
            'message' => 'Related posts retrieved successfully'
        ]);
    }

    
    

    public function next(Post $post): JsonResponse
    {
        $nextPost = $post->getNextPost();

        return response()->json([
            'success' => true,
            'data' => $nextPost,
            'message' => 'Next post retrieved successfully'
        ]);
    }

   
    

    public function previous(Post $post): JsonResponse
    {
        $previousPost = $post->getPreviousPost();

        return response()->json([
            'success' => true,
            'data' => $previousPost,
            'message' => 'Previous post retrieved successfully'
        ]);
    }

    
    

    public function like(Post $post): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Post already liked'
            ], 422);
        }

        $post->likes()->attach($user->id);
        $post->incrementLikeCount();

        return response()->json([
            'success' => true,
            'data' => [
                'like_count' => $post->like_count
            ],
            'message' => 'Post liked successfully'
        ]);
    }

   
    

    public function unlike(Post $post): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$post->likes()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Post not liked'
            ], 422);
        }

        $post->likes()->detach($user->id);
        $post->decrementLikeCount();

        return response()->json([
            'success' => true,
            'data' => [
                'like_count' => $post->like_count
            ],
            'message' => 'Post unliked successfully'
        ]);
    }
}

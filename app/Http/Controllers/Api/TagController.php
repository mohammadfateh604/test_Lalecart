<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::with(['posts']);

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->search($search);
        }

        // Popular tags
        if ($request->has('popular')) {
            $limit = $request->get('limit', 10);
            $query->popular($limit);
        }

        // Order by
        $orderBy = $request->get('order_by', 'post_count');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tags = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tags,
            'message' => 'Tags retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $tag = Tag::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $tag,
            'message' => 'Tag created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->load(['posts']);

        return response()->json([
            'success' => true,
            'data' => $tag,
            'message' => 'Tag retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $tag->id,
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $tag->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $tag,
            'message' => 'Tag updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }

    /**
     * Get tag posts
     */
    public function posts(Tag $tag, Request $request): JsonResponse
    {
        $query = $tag->posts()->with(['author', 'category']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        // Search in posts
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
            'message' => 'Tag posts retrieved successfully'
        ]);
    }

    /**
     * Get popular posts for tag
     */
    public function popularPosts(Tag $tag, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $posts = $tag->getPopularPosts($limit);

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Popular posts for tag retrieved successfully'
        ]);
    }

    /**
     * Get recent posts for tag
     */
    public function recentPosts(Tag $tag, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $posts = $tag->getRecentPosts($limit);

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Recent posts for tag retrieved successfully'
        ]);
    }

    /**
     * Get random posts for tag
     */
    public function randomPosts(Tag $tag, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $posts = $tag->getRandomPosts($limit);

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Random posts for tag retrieved successfully'
        ]);
    }

    /**
     * Get related tags
     */
    public function related(Tag $tag, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $relatedTags = $tag->getRelatedTags($limit);

        return response()->json([
            'success' => true,
            'data' => $relatedTags,
            'message' => 'Related tags retrieved successfully'
        ]);
    }

    /**
     * Get tag statistics
     */
    public function statistics(Tag $tag, Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);
        $monthlyStats = $tag->getMonthlyStats($year);
        $yearlyStats = $tag->getYearlyStats();

        $statistics = [
            'post_count' => $tag->post_count,
            'published_posts_count' => $tag->published_posts_count,
            'monthly_stats' => $monthlyStats,
            'yearly_stats' => $yearlyStats,
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Tag statistics retrieved successfully'
        ]);
    }

    /**
     * Get popular tags
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $tags = Tag::getPopularTags($limit);

        return response()->json([
            'success' => true,
            'data' => $tags,
            'message' => 'Popular tags retrieved successfully'
        ]);
    }

    /**
     * Get tags with post count
     */
    public function withPostCount(Request $request): JsonResponse
    {
        $limit = $request->get('limit', null);
        $tags = Tag::getTagsWithPostCount($limit);

        return response()->json([
            'success' => true,
            'data' => $tags,
            'message' => 'Tags with post count retrieved successfully'
        ]);
    }

    /**
     * Find or create tag by name
     */
    public function findOrCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $tag = Tag::findOrCreateByName($request->name);

        return response()->json([
            'success' => true,
            'data' => $tag,
            'message' => 'Tag found or created successfully'
        ]);
    }

    /**
     * Find or create multiple tags by names
     */
    public function findOrCreateMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'names' => 'required|array',
            'names.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $tags = Tag::findOrCreateByNames($request->names);

        return response()->json([
            'success' => true,
            'data' => $tags,
            'message' => 'Tags found or created successfully'
        ]);
    }
}

# API Documentation
### Categories API

#### Get All Categories
```
GET /api/categories
```

**Query Parameters:**
- `active` (boolean): Filter by active status
- `featured` (boolean): Filter by featured status
- `root` (boolean): Get only root categories
- `parent_id` (integer): Filter by parent category
- `search` (string): Search in name and description
- `order_by` (string): Order by field (default: sort_order)
- `order_direction` (string): asc or desc (default: asc)
- `per_page` (integer): Items per page (default: 15)

#### Create Category
```
POST /api/categories
```

**Body:**
```json
{
    "name": "Category Name",
    "description": "Category Description",
    "image": "image_file",
    "color": "#FF0000",
    "is_active": true,
    "is_featured": false,
    "sort_order": 1,
    "parent_id": null
}
```

#### Get Category
```
GET /api/categories/{id}
```

#### Update Category
```
PUT /api/categories/{id}
```

#### Delete Category
```
DELETE /api/categories/{id}
```

#### Get Category Posts
```
GET /api/categories/{id}/posts
```

#### Get Category Children
```
GET /api/categories/{id}/children
```

#### Get Category Breadcrumb
```
GET /api/categories/{id}/breadcrumb
```

### Posts API

#### Get All Posts
```
GET /api/posts
```

**Query Parameters:**
- `status` (string): Filter by status (draft, published, archived)
- `visibility` (string): Filter by visibility (public, private, password_protected)
- `category_id` (integer): Filter by category
- `author_id` (integer): Filter by author
- `tag_id` (integer): Filter by tag
- `featured` (boolean): Filter by featured posts
- `sticky` (boolean): Filter by sticky posts
- `search` (string): Search in title and content
- `popular` (boolean): Get popular posts
- `recent` (boolean): Get recent posts
- `order_by` (string): Order by field (default: published_at)
- `order_direction` (string): asc or desc (default: desc)
- `per_page` (integer): Items per page (default: 15)

#### Create Post
```
POST /api/posts
```

**Body:**
```json
{
    "title": "Post Title",
    "content": "Post Content",
    "excerpt": "Post Excerpt",
    "featured_image": "image_file",
    "meta_title": "Meta Title",
    "meta_description": "Meta Description",
    "meta_keywords": ["keyword1", "keyword2"],
    "status": "published",
    "visibility": "public",
    "allow_comments": true,
    "is_featured": false,
    "is_sticky": false,
    "published_at": "2024-01-01 12:00:00",
    "author_id": 1,
    "category_id": 1,
    "tags": [1, 2, 3]
}
```

#### Get Post
```
GET /api/posts/{id}
```

#### Update Post
```
PUT /api/posts/{id}
```

#### Delete Post
```
DELETE /api/posts/{id}
```

#### Publish Post
```
POST /api/posts/{id}/publish
```

#### Unpublish Post
```
POST /api/posts/{id}/unpublish
```

#### Archive Post
```
POST /api/posts/{id}/archive
```

#### Get Related Posts
```
GET /api/posts/{id}/related?limit=5
```

#### Get Next Post
```
GET /api/posts/{id}/next
```

#### Get filter title Post
```
GET /api/posts?title=php
```

#### Get Previous Post
```
GET /api/posts/{id}/previous
```

#### Like Post
```
POST /api/posts/{id}/like
```

#### Unlike Post
```
POST /api/posts/{id}/unlike
```

### Tags API

#### Get All Tags
```
GET /api/tags
```

**Query Parameters:**
- `active` (boolean): Filter by active status
- `search` (string): Search in name and description
- `popular` (boolean): Get popular tags
- `limit` (integer): Limit for popular tags (default: 10)
- `order_by` (string): Order by field (default: post_count)
- `order_direction` (string): asc or desc (default: desc)
- `per_page` (integer): Items per page (default: 15)

#### Create Tag
```
POST /api/tags
```

**Body:**
```json
{
    "name": "Tag Name",
    "name_en": "English Name",
    "name_ar": "Arabic Name",
    "description": "Tag Description",
    "color": "#FF0000",
    "is_active": true
}
```

#### Get Tag
```
GET /api/tags/{id}
```

#### Update Tag
```
PUT /api/tags/{id}
```

#### Delete Tag
```
DELETE /api/tags/{id}
```

#### Get Tag Posts
```
GET /api/tags/{id}/posts
```

#### Get Popular Posts for Tag
```
GET /api/tags/{id}/popular-posts?limit=5
```

#### Get Recent Posts for Tag
```
GET /api/tags/{id}/recent-posts?limit=5
```

#### Get Random Posts for Tag
```
GET /api/tags/{id}/random-posts?limit=5
```

#### Get Related Tags
```
GET /api/tags/{id}/related?limit=5
```

#### Get Tag Statistics
```
GET /api/tags/{id}/statistics?year=2024
```

#### Get Popular Tags
```
GET /api/tags/popular?limit=10
```

#### Get Tags with Post Count
```
GET /api/tags/with-post-count?limit=10
```

#### Find or Create Tag
```
POST /api/tags/find-or-create
```

**Body:**
```json
{
    "name": "Tag Name"
}
```

#### Find or Create Multiple Tags
```
POST /api/tags/find-or-create-multiple
```

**Body:**
```json
{
    "names": ["Tag 1", "Tag 2", "Tag 3"]
}
```

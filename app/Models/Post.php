<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\User;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'visibility',
        'password',
        'allow_comments',
        'is_featured',
        'is_sticky',
        'view_count',
        'like_count',
        'comment_count',
        'published_at',
        'author_id',
        'category_id',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
        'allow_comments' => 'boolean',
        'is_featured' => 'boolean',
        'is_sticky' => 'boolean',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'published_at' => 'datetime',
        'author_id' => 'integer',
        'category_id' => 'integer',
    ];

    protected $appends = [
        'reading_time',
        'formatted_published_date',
        'featured_image_url',
    ];

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSticky($query)
    {
        return $query->where('is_sticky', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    public function scopePopular($query, $days = 30)
    {
        return $query->where('published_at', '>=', now()->subDays($days))
                    ->orderBy('view_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getReadingTimeAttribute(): int
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    public function getFormattedPublishedDateAttribute(): string
    {
        if ($this->published_at) {
            return $this->published_at->format('F j, Y');
        }
        return 'Not published';
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }
        return null;
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        // Generate excerpt from content if not set
        $content = strip_tags($this->content);
        return Str::limit($content, 200);
    }

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: $this->excerpt;
    }

    public function getMetaKeywordsStringAttribute(): string
    {
        if (is_array($this->meta_keywords)) {
            return implode(', ', $this->meta_keywords);
        }
        return $this->meta_keywords ?? '';
    }

    public function getCommentCountAttribute($value): int
    {
        // If the value is not set or is 0, calculate it dynamically
        if (!$value || $value == 0) {
            return $this->comments()->count();
        }
        return $value;
    }

    public function getLikeCountAttribute()
    {
        return $this->likes()->count();
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function setPublishedAtAttribute($value)
    {
        if ($value && $this->status === 'published') {
            $this->attributes['published_at'] = $value;
        }
    }

    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isPasswordProtected(): bool
    {
        return $this->visibility === 'password_protected';
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementLikeCount(): void
    {
        $this->increment('like_count');
    }

    public function decrementLikeCount(): void
    {
        $this->decrement('like_count');
    }

    public function updateCommentCount(): void
    {
        $this->comment_count = $this->comments()->count();
        $this->save();
    }

    public static function updateAllCommentCounts(): void
    {
        $posts = self::all();
        foreach ($posts as $post) {
            $post->updateCommentCount();
        }
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function getRelatedPosts($limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('category_id', $this->category_id)
                      ->orWhereHas('tags', function ($q) {
                          $q->whereIn('tags.id', $this->tags->pluck('id'));
                      });
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getNextPost(): ?Post
    {
        return static::published()
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }

    public function getPreviousPost(): ?Post
    {
        return static::published()
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    public function getTagNames(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    public function getMetaKeywordsArray(): array
    {
        return $this->meta_keywords ?: [];
    }

    // Boot method for automatic slug generation and comment count updates
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::saved(function ($post) {
            // Update comment count when comments are saved
            if ($post->wasChanged('comment_count')) {
                $post->updateCommentCount();
            }
        });
    }
}

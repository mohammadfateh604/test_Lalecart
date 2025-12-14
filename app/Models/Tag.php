<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'slug',
        'slug_en',
        'slug_ar',
        'description',
        'description_en',
        'description_ar',
        'color',
        'is_active',
        'post_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'post_count' => 'integer',
    ];

    protected $appends = [
        'published_posts_count',
    ];

    // Relationships
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function publishedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
                    ->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('post_count', 'desc')->limit($limit);
    }

    public function scopeWithPostCount($query)
    {
        return $query->withCount('posts');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    // Accessors
    public function getPublishedPostsCountAttribute(): int
    {
        return $this->posts()->published()->count();
    }

    public function getColorClassAttribute(): string
    {
        // Convert hex color to Tailwind CSS class
        $colorMap = [
            '#3B82F6' => 'bg-blue-500',
            '#10B981' => 'bg-green-500',
            '#F59E0B' => 'bg-yellow-500',
            '#EF4444' => 'bg-red-500',
            '#8B5CF6' => 'bg-purple-500',
            '#F97316' => 'bg-orange-500',
            '#06B6D4' => 'bg-cyan-500',
            '#EC4899' => 'bg-pink-500',
            '#6B7280' => 'bg-gray-500',
            '#84CC16' => 'bg-lime-500',
        ];

        return $colorMap[$this->color] ?? 'bg-gray-500';
    }

    public function getTextColorClassAttribute(): string
    {
        // Convert hex color to Tailwind CSS text class
        $colorMap = [
            '#3B82F6' => 'text-blue-500',
            '#10B981' => 'text-green-500',
            '#F59E0B' => 'text-yellow-500',
            '#EF4444' => 'text-red-500',
            '#8B5CF6' => 'text-purple-500',
            '#F97316' => 'text-orange-500',
            '#06B6D4' => 'text-cyan-500',
            '#EC4899' => 'text-pink-500',
            '#6B7280' => 'text-gray-500',
            '#84CC16' => 'text-lime-500',
        ];

        return $colorMap[$this->color] ?? 'text-gray-500';
    }

    public function getBorderColorClassAttribute(): string
    {
        // Convert hex color to Tailwind CSS border class
        $colorMap = [
            '#3B82F6' => 'border-blue-500',
            '#10B981' => 'border-green-500',
            '#F59E0B' => 'border-yellow-500',
            '#EF4444' => 'border-red-500',
            '#8B5CF6' => 'border-purple-500',
            '#F97316' => 'border-orange-500',
            '#06B6D4' => 'border-cyan-500',
            '#EC4899' => 'border-pink-500',
            '#6B7280' => 'border-gray-500',
            '#84CC16' => 'border-lime-500',
        ];

        return $colorMap[$this->color] ?? 'border-gray-500';
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasPosts(): bool
    {
        return $this->post_count > 0;
    }

    // حذف یا تغییر این تابع:
public function updatePostCount(): void
{
    $this->post_count = $this->posts()->count();
    // فقط مقدار رو تنظیم کن، ذخیره نکن
}

// و در boot():



    public function getRelatedTags($limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereHas('posts', function ($query) {
            $query->whereIn('posts.id', $this->posts->pluck('id'));
        })
        ->where('id', '!=', $this->id)
        ->orderBy('post_count', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getPopularPosts($limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->publishedPosts()
                    ->orderBy('view_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public function getRecentPosts($limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->publishedPosts()
                    ->orderBy('published_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public function getRandomPosts($limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->publishedPosts()
                    ->inRandomOrder()
                    ->limit($limit)
                    ->get();
    }

    public function getPostCountByMonth($year = null, $month = null): int
    {
        $query = $this->posts()->where('status', 'published');
        
        if ($year && $month) {
            $query->whereYear('published_at', $year)
                  ->whereMonth('published_at', $month);
        } elseif ($year) {
            $query->whereYear('published_at', $year);
        }
        
        return $query->count();
    }

    public function getPostCountByYear($year): int
    {
        return $this->getPostCountByMonth($year);
    }

    public function getMonthlyStats($year = null): array
    {
        $year = $year ?: now()->year;
        $stats = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $stats[$month] = $this->getPostCountByMonth($year, $month);
        }
        
        return $stats;
    }

    public function getYearlyStats(): array
    {
        $currentYear = now()->year;
        $stats = [];
        
        for ($year = $currentYear - 5; $year <= $currentYear; $year++) {
            $stats[$year] = $this->getPostCountByYear($year);
        }
        
        return $stats;
    }

    // Static methods
    public static function getPopularTags($limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->popular($limit)
                    ->get();
    }

    public static function getTagsWithPostCount($limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active()->withPostCount()->orderBy('post_count', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    public static function findOrCreateByName($name): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );
    }

    public static function findOrCreateByNames(array $names): \Illuminate\Database\Eloquent\Collection
    {
        $tags = collect();
        
        foreach ($names as $name) {
            $tags->push(static::findOrCreateByName($name));
        }
        
        return $tags;
    }

    // Boot method for automatic slug generation
    protected static function boot()
{
    parent::boot();

    static::creating(function ($tag) {
        if (empty($tag->slug)) {
            $tag->slug = Str::slug($tag->name);
        }
    });

    static::updating(function ($tag) {
        if ($tag->isDirty('name') && empty($tag->slug)) {
            $tag->slug = Str::slug($tag->name);
        }
    });

    // ✅ نسخه اصلاح‌شده بدون لوپ
    static::saved(function ($tag) {
        $count = $tag->posts()->count();

        if ($tag->post_count !== $count) {
            $tag->updateQuietly(['post_count' => $count]);
        }
    });
}
}

<?php

namespace App\Repositories;

use App\Models\Post;
use App\Queries\GetPostBySlugQuery;
use App\Queries\GetPostsQuery;
use App\Repositories\Contracts\PostRepositoryInterface;
use Firefly\FilamentBlog\Enums\PostStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    public function getPosts(GetPostsQuery $query): LengthAwarePaginator
    {
        $builder = Post::query()
            ->with(['project', 'user', 'categories', 'tags'])
            ->where('status', PostStatus::from($query->status));

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query->search}%")
                    ->orWhere('sub_title', 'like', "%{$query->search}%")
                    ->orWhere('body', 'like', "%{$query->search}%");
            });
        }

        if ($query->project_id) {
            $builder->where('project_id', $query->project_id);
        }

        if ($query->category_id) {
            $builder->whereHas('categories', function ($q) use ($query) {
                $q->where(config('filamentblog.tables.prefix').'categories', $query->category_id);
            });
        }

        if ($query->tag_id) {
            $builder->whereHas('tags', function ($q) use ($query) {
                $q->where(config('filamentblog.tables.prefix').'tags', $query->tag_id);
            });
        }

        if ($query->sort_by) {
            $builder->orderBy($query->sort_by, $query->sort_direction);
        }

        return $builder->paginate(
            perPage: $query->per_page,
            page: $query->page
        );
    }

    public function getPostBySlug(GetPostBySlugQuery $query): ?Post
    {
        return Post::query()
            ->with(['project', 'user', 'categories', 'tags'])
            ->where('slug', $query->slug)
            ->where('status', PostStatus::PUBLISHED)
            ->first();
    }
}


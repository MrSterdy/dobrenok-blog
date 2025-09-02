<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use App\Queries\GetPostBySlugQuery;
use App\Queries\GetPostsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function getPosts(GetPostsQuery $query): LengthAwarePaginator;

    public function getPostBySlug(GetPostBySlugQuery $query): ?Post;
}


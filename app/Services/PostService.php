<?php

namespace App\Services;

use App\DTOs\PaginatedResponseDTO;
use App\DTOs\PostDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\UserDTO;
use App\Models\Post;
use App\Queries\GetPostBySlugQuery;
use App\Queries\GetPostsQuery;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostService
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository
    ) {}

    public function getPosts(GetPostsQuery $query): PaginatedResponseDTO
    {
        $posts = $this->postRepository->getPosts($query);

        $postDTOs = $posts->items();
        $postDTOs = array_map(function (Post $post) {
            return $this->mapPostToDTO($post);
        }, $postDTOs);

        return new PaginatedResponseDTO(
            data: $postDTOs,
            current_page: $posts->currentPage(),
            last_page: $posts->lastPage(),
            per_page: $posts->perPage(),
            total: $posts->total(),
            next_page_url: $posts->nextPageUrl(),
            prev_page_url: $posts->previousPageUrl(),
        );
    }

    public function getPostBySlug(GetPostBySlugQuery $query): ?PostDTO
    {
        $post = $this->postRepository->getPostBySlug($query);

        return $post ? $this->mapPostToDTO($post) : null;
    }

    private function mapPostToDTO(Post $post): PostDTO
    {
        return new PostDTO(
            id: $post->id,
            title: $post->title,
            slug: $post->slug,
            sub_title: $post->sub_title,
            body: $post->body,
            status: $post->status->value,
            published_at: $post->published_at?->toISOString(),
            cover_photo_url: $post->cover_photo_path ? asset('storage/' . $post->cover_photo_path) : '',
            photo_alt_text: $post->photo_alt_text,
            project: $post->project ? $this->mapProjectToDTO($post->project) : null,
            author: $this->mapUserToDTO($post->user),
            categories: $post->categories->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->toArray(),
            tags: $post->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ])->toArray(),
            created_at: $post->created_at->toISOString(),
            updated_at: $post->updated_at->toISOString(),
        );
    }

    private function mapProjectToDTO($project): ProjectDTO
    {
        return new ProjectDTO(
            id: $project->id,
            name: $project->name,
            description: $project->description,
            cover_photo_url: $project->cover_photo_path ? asset('storage/' . $project->cover_photo_path) : '',
            home_url: $project->home_url,
            posts_count: $project->posts_count ?? 0,
            payment_goal: null,
            created_at: $project->created_at->toISOString(),
            updated_at: $project->updated_at->toISOString(),
        );
    }

    private function mapUserToDTO($user): UserDTO
    {
        return new UserDTO(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}

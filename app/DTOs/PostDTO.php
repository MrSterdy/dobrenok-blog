<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Post",
    type: "object",
    description: "Пост блога",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID поста", example: 1),
        new OA\Property(property: "title", type: "string", description: "Заголовок поста", example: "Мой первый пост"),
        new OA\Property(property: "slug", type: "string", description: "URL-slug поста", example: "moy-pervyy-post"),
        new OA\Property(property: "sub_title", type: "string", nullable: true, description: "Подзаголовок поста", example: "Подзаголовок поста"),
        new OA\Property(property: "body", type: "string", description: "Содержание поста", example: "Содержание поста..."),
        new OA\Property(property: "status", type: "string", description: "Статус поста", example: "published"),
        new OA\Property(property: "published_at", type: "string", format: "date-time", nullable: true, description: "Дата публикации", example: "2024-01-15T10:30:00Z"),
        new OA\Property(property: "cover_photo_url", type: "string", description: "URL обложки поста", example: "http://localhost:8000/storage/blog-feature-images/image.jpg"),
        new OA\Property(property: "photo_alt_text", type: "string", description: "Alt-текст для изображения", example: "Описание изображения"),
        new OA\Property(property: "project", ref: "#/components/schemas/Project", nullable: true, description: "Связанный проект"),
        new OA\Property(property: "author", ref: "#/components/schemas/User", description: "Автор поста"),
        new OA\Property(
            property: "categories",
            type: "array",
            description: "Категории поста",
            items: new OA\Items(ref: "#/components/schemas/Category")
        ),
        new OA\Property(
            property: "tags",
            type: "array",
            description: "Теги поста",
            items: new OA\Items(ref: "#/components/schemas/Tag")
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания", example: "2024-01-15T10:30:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления", example: "2024-01-15T10:30:00Z"),
    ]
)]
readonly class PostDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $sub_title,
        public string $body,
        public string $status,
        public ?string $published_at,
        public string $cover_photo_url,
        public string $photo_alt_text,
        public ?ProjectDTO $project,
        public UserDTO $author,
        public array $categories,
        public array $tags,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sub_title' => $this->sub_title,
            'body' => $this->body,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'cover_photo_url' => $this->cover_photo_url,
            'photo_alt_text' => $this->photo_alt_text,
            'project' => $this->project?->toArray(),
            'author' => $this->author->toArray(),
            'categories' => $this->categories,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Constants;

class ApiConstants
{
    // API версии
    public const API_VERSION_V1 = 'v1';

    // Статусы HTTP
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    // Пагинация
    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 50;
    public const MIN_PER_PAGE = 1;

    // Сортировка
    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    // Статусы постов
    public const POST_STATUS_PUBLISHED = 'published';
    public const POST_STATUS_PENDING = 'pending';
    public const POST_STATUS_SCHEDULED = 'scheduled';

    // Типы ошибок
    public const ERROR_TYPE_VALIDATION = 'validation_error';
    public const ERROR_TYPE_NOT_FOUND = 'not_found';
    public const ERROR_TYPE_SERVER = 'server_error';
    public const ERROR_TYPE_UNAUTHORIZED = 'unauthorized';

    // Сообщения ошибок
    public const ERROR_PROJECT_NOT_FOUND = 'Project not found';
    public const ERROR_POST_NOT_FOUND = 'Post not found';
    public const ERROR_INVALID_PARAMETERS = 'Invalid parameters provided';
    public const ERROR_SERVER_ERROR = 'Internal server error';

    // Сообщения успеха
    public const SUCCESS_PROJECTS_RETRIEVED = 'Projects retrieved successfully';
    public const SUCCESS_PROJECT_RETRIEVED = 'Project retrieved successfully';
    public const SUCCESS_POSTS_RETRIEVED = 'Posts retrieved successfully';
    public const SUCCESS_POST_RETRIEVED = 'Post retrieved successfully';
}

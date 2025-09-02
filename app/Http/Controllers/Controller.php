<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Dobrenok Blog API",
    description: "API для получения проектов и постов блога. Включает пагинацию, поиск, фильтрацию и сортировку.",
    contact: new OA\Contact(
        name: "API Support",
        email: "support@dobrenok.com"
    )
)]
#[OA\Server(
    url: "http://localhost:8000/api/v1",
    description: "Local development server"
)]
#[OA\Server(
    url: "https://api.dobrenok.com/api/v1",
    description: "Production server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Schema(
    schema: "PaginationMeta",
    type: "object",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "last_page", type: "integer", example: 5),
        new OA\Property(property: "per_page", type: "integer", example: 15),
        new OA\Property(property: "total", type: "integer", example: 75),
        new OA\Property(property: "next_page_url", type: "string", nullable: true, example: "http://localhost:8000/api/v1/projects?page=2"),
        new OA\Property(property: "prev_page_url", type: "string", nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: "SuccessResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
        new OA\Property(property: "data", type: "object"),
        new OA\Property(property: "meta", ref: "#/components/schemas/PaginationMeta")
    ]
)]
#[OA\Schema(
    schema: "ErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(
            property: "error",
            type: "object",
            properties: [
                new OA\Property(property: "type", type: "string", example: "not_found"),
                new OA\Property(property: "message", type: "string", example: "Resource not found"),
                new OA\Property(property: "details", type: "object", nullable: true)
            ]
        )
    ]
)]
abstract class Controller
{
    //
}

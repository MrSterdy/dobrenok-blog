<?php

namespace App\Services\SocialMedia;

readonly class SocialMediaPublishResult
{
    public function __construct(
        public bool $success,
        public ?string $external_id = null,
        public ?string $external_url = null,
        public ?string $error_message = null,
    ) {}

    public static function success(string $externalId, string $externalUrl): self
    {
        return new self(
            success: true,
            external_id: $externalId,
            external_url: $externalUrl
        );
    }

    public static function failure(string $errorMessage): self
    {
        return new self(
            success: false,
            error_message: $errorMessage
        );
    }
}

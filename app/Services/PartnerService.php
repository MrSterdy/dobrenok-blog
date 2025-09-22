<?php

namespace App\Services;

use App\DTOs\PaginatedResponseDTO;
use App\DTOs\PartnerDTO;
use App\Models\Partner;
use App\Queries\GetPartnersQuery;
use App\Repositories\Contracts\PartnerRepositoryInterface;

class PartnerService
{
    public function __construct(
        private readonly PartnerRepositoryInterface $partnerRepository
    ) {}

    public function getPartners(GetPartnersQuery $query): PaginatedResponseDTO
    {
        $partners = $this->partnerRepository->getPartners($query);

        $partnerDTOs = $partners->items();
        $partnerDTOs = array_map(function (Partner $partner) {
            return $this->mapPartnerToDTO($partner);
        }, $partnerDTOs);

        return new PaginatedResponseDTO(
            data: $partnerDTOs,
            current_page: $partners->currentPage(),
            last_page: $partners->lastPage(),
            per_page: $partners->perPage(),
            total: $partners->total(),
            next_page_url: $partners->nextPageUrl(),
            prev_page_url: $partners->previousPageUrl(),
        );
    }

    private function mapPartnerToDTO(Partner $partner): PartnerDTO
    {
        return new PartnerDTO(
            id: $partner->id,
            name: $partner->name,
            description: $partner->description,
            cover_photo_url: $partner->cover_photo_path ? asset('storage/' . $partner->cover_photo_path) : '',
            created_at: $partner->created_at->toISOString(),
            updated_at: $partner->updated_at->toISOString(),
        );
    }
}

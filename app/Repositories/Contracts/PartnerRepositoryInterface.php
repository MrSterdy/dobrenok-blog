<?php

namespace App\Repositories\Contracts;

use App\Models\Partner;
use App\Queries\GetPartnersQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PartnerRepositoryInterface
{
    public function getPartners(GetPartnersQuery $query): LengthAwarePaginator;
}

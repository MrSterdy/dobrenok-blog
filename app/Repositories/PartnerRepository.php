<?php

namespace App\Repositories;

use App\Models\Partner;
use App\Queries\GetPartnersQuery;
use App\Repositories\Contracts\PartnerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PartnerRepository implements PartnerRepositoryInterface
{
    public function getPartners(GetPartnersQuery $query): LengthAwarePaginator
    {
        $builder = Partner::query()->with('project');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query->search}%")
                    ->orWhere('description', 'like', "%{$query->search}%");
            });
        }

        if ($query->project_id) {
            $builder->where('project_id', $query->project_id);
        }

        if ($query->sort_by) {
            $builder->orderBy($query->sort_by, $query->sort_direction);
        }

        return $builder->paginate(
            perPage: $query->per_page,
            page: $query->page
        );
    }
}

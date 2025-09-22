<?php

namespace App\Repositories;

use App\Models\Event;
use App\Queries\GetEventsQuery;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function getEvents(GetEventsQuery $query): LengthAwarePaginator
    {
        $builder = Event::query()->with('project');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query->search}%")
                    ->orWhere('body', 'like', "%{$query->search}%");
            });
        }

        if ($query->project_id) {
            $builder->where('project_id', $query->project_id);
        }

        if ($query->month) {
            $builder->whereMonth('start_date', $query->month);
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

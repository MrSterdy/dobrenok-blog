<?php

namespace App\Services;

use App\DTOs\EventDTO;
use App\DTOs\PaginatedResponseDTO;
use App\Models\Event;
use App\Queries\GetEventsQuery;
use App\Repositories\Contracts\EventRepositoryInterface;

class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository
    ) {}

    public function getEvents(GetEventsQuery $query): PaginatedResponseDTO
    {
        $events = $this->eventRepository->getEvents($query);

        $eventDTOs = $events->items();
        $eventDTOs = array_map(function (Event $event) {
            return $this->mapEventToDTO($event);
        }, $eventDTOs);

        return new PaginatedResponseDTO(
            data: $eventDTOs,
            current_page: $events->currentPage(),
            last_page: $events->lastPage(),
            per_page: $events->perPage(),
            total: $events->total(),
            next_page_url: $events->nextPageUrl(),
            prev_page_url: $events->previousPageUrl(),
        );
    }

    private function mapEventToDTO(Event $event): EventDTO
    {
        return new EventDTO(
            id: $event->id,
            name: $event->name,
            slug: $event->slug,
            short_description: $event->short_description,
            body: $event->body,
            cover_photo_url: $event->cover_photo_path ? asset('storage/' . $event->cover_photo_path) : '',
            start_date: $event->start_date->toISOString(),
            created_at: $event->created_at->toISOString(),
            updated_at: $event->updated_at->toISOString(),
        );
    }
}

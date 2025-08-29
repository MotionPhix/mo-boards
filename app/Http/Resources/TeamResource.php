<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        return [
          'data' => $this->resource->getCollection()->values()->all(),
          'meta' => [
            'current_page' => $this->resource->currentPage(),
            'last_page' => $this->resource->lastPage(),
            'per_page' => $this->resource->perPage(),
            'total' => $this->resource->total(),
            'from' => $this->resource->firstItem(),
            'to' => $this->resource->lastItem(),
          ],
          'links' => [
            'first' => $this->resource->url(1),
            'last' => $this->resource->url($this->resource->lastPage()),
            'prev' => $this->resource->previousPageUrl(),
            'next' => $this->resource->nextPageUrl(),
          ]
        ];
      }

      // Handle collections (your current case)
      if ($this->resource instanceof \Illuminate\Support\Collection) {
        return [
          'data' => $this->resource->values()->all()
        ];
      }

      // Handle arrays (already transformed data from controller)
      if (is_array($this->resource)) {
        return [
          'data' => array_values($this->resource)
        ];
      }

      // Fallback - wrap whatever we got in data
      return [
        'data' => $this->resource
      ];
    }
}

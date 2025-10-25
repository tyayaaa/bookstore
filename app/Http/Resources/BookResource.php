<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request)
    {
        $trending = '-';
        if (!is_null($this->avg_rating_last_7_days) && !is_null($this->ratings_avg_rating)) {
            if ($this->avg_rating_last_7_days > $this->ratings_avg_rating) {
                $trending = '↑';
            } elseif ($this->avg_rating_last_7_days < $this->ratings_avg_rating) {
                $trending = '↓';
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'author' => $this->author->name ?? null,
            'category' => $this->category->name ?? null,
            'average_rating' => round($this->ratings_avg_rating, 2),
            'total_voters' => $this->ratings_count,
            'trending' => $trending,
            'availability_status' => $this->stock > 0 ? 'available' : 'rented',
        ];
    }
}

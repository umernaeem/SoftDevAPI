<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CakeDaysResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'cake_date' => $this->cake_date,
            'no_of_cakes' => $this->no_of_cakes,
            'cake_type' => ucfirst($this->cake_type),
            'developer_names' => $this->developer_names,
        ];
    }
}

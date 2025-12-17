<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    public $collects = PostResource::class;

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->resource->currentPage(),
                'last_page'    => $this->resource->lastPage(),
                'per_page'     => $this->resource->perPage(),
                'total'        => $this->resource->total(),
            ],
        ];
    }
}

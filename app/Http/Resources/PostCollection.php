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
            // 'meta' => [
            //     'current_page' => $this->$request->currentPage(),
            //     'last_page'    => $this->$request->lastPage(),
            //     'per_page'     => $this->$request->perPage(),
            //     'total'        => $this->$request->total(),
            // ],
        ];
    }
}

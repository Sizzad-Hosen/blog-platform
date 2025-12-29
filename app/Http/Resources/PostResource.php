<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CommentResource;

class PostResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'body' => $this->body,
        'category_id' => $this->category_id,
        'user_id' => $this->user_id,
        'created_at' => $this->created_at,
    ];
}

}

<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
   public function delete(User $user, Post $post)
{
    return $user->id === $post->user_id || $user->role === 'admin';
}

public function forceDelete(User $user, Post $post)
{
    return $user->role === 'admin';
}

public function restore(User $user, Post $post)
{
    return $user->role === 'admin';
}

public function update(User $user, Post $post)
{
    return $user->id === $post->user_id || $user->role === 'admin';
}

}

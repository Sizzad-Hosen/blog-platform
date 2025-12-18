<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use App\Models\User;

class UserController extends Controller
{
    public function users(){
        $users = User::latest()->get();
        $posts = Post::latest()->get();

        foreach($users as $user){
            $user->posts_count = $posts->where('user_id', $user->id)->count();
        };

    }

    
    public function profile(){
        
        $user = Auth::user();

    }


}

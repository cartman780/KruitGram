<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Post;

class PostsController extends Controller
{
    // This construct function makes al the functions in this controller require authorization.
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Pluck the auth user, so you can see the page without loging in
        $users = auth()->user()->following()->pluck('profiles.user_id');

        // Get the postst with user and show 5 per pae
        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate(5);

        return view('posts.index', compact('posts'));
    }
    
    public function create()
    {
        return view('posts.create');
    }
    
    public function store()
    {
        // validation
        $data = request()->validate([
            'caption' => 'required',
            'image' => 'required|image',
        ]);
        
        // where to store files (also do php artisan storage:link)
        $imagePath = request('image')->store('uploads', 'public');

        // resize image (also needed composer require intervention/image )
        $image = Image::make(public_path("storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();

        // user autherization
        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);

        return redirect('/profile/'. auth()->user()->id);
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}

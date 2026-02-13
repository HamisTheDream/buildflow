<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->latest('published_at')
            ->paginate(9);

        return Inertia::render('Public/Blog/Index', [
            'posts' => $posts
        ]);
    }

    public function show($slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        return Inertia::render('Public/Blog/Show', [
            'post' => $post,
            'related' => Post::published()
                ->where('id', '!=', $post->id)
                ->limit(3)
                ->get()
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;

class ProfilesController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($user)
    {
        $user = User::findOrFail($user);
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

        $postsCount = Cache::remember(
            'count.posts' . $user->id,
            now()->addSeconds(60),
            function() use ($user){
                return $user->posts->count();
            });

        $followersCount = Cache::remember(
            'count.followers' . $user->id,
            now()->addSeconds(60),
            function() use ($user){
                $user->profile->followers->count();
            });

        $followingCount = Cache::remember(
            'count.following' . $user->id,
            now()->addSeconds(60),
            function() use ($user){
                $user->following->count();
            });

        return view('profiles.index', [
            'user' => $user,
            'follows' => $follows,
            'postsCount' => $postsCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);
        return view('profiles.edit', compact('user'));
    }

    public function update(User $user)
    {
        $this->authorize('update', $user->profile);
        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);

        if(request('image')) {
            $imagePath = request('image')->store('profile', 'public');
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000,1000);
            $image->save();
            $data['image'] = $imagePath;
        }

        Auth::user()->profile->update($data);

        return redirect("profile/{$user->id}");
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\OurExampleEvent;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:3000'
        ]);

        $user = auth()->user();
        $fileName = $user->id . '-' . uniqid() . '.jpg';

        $imageData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $fileName, $imageData);

        $oldAvatar = $user->avatar;
        $user->avatar = $fileName;
        $user->save();

        if ($oldAvatar != '/fallback-avatar.jpg') {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return back()->with('success', 'Congrats on the new Avatar.');
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

    private function getSharedProfileData(User $user) {
        $currentlyFollowing = 0;

        if (auth()->check()) {
            $currentlyFollowing = Follow::where([
                ['user_id', '=', auth()->user()->id],
                ['followeduser', '=', $user->id],
            ])->count();
        }

        View::share(
            'sharedProfileData',
            [
                'username' => $user->username,
                'postCount' => $user->posts()->count(),
                'followersCount' => $user->followers()->count(),
                'followingCount' => $user->followingTheseUsers()->count(),
                'avatar' => $user->avatar,
                'currentlyFollowing' => $currentlyFollowing
            ]
        );
    }

    public function profile(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-posts',
            [
                'posts' => $user->posts()->latest()->get(),
            ]
        );
    }

    public function profileRaw(User $user) {
        return response()->json([
            'theHTML' => view(
                'profile-posts-only',
                ['posts'=>$user->posts()->latest()->get()]
            )->render(),
            'docTitle' => $user->username . "'s Profile"
        ]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-followers',
            [
                'followers' => $user->followers()->latest()->get(),
            ]
        );
    }

    public function profileFollowersRaw(User $user) {
        return response()->json([
            'theHTML' => view(
                'profile-followers-only',
                ['followers' => $user->followers()->latest()->get()]
            )->render(),
            'docTitle' => $user->username . "'s Followers"
        ]);
    }

    public function profileFollowing(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-following',
            [
                'following' => $user->followingTheseUsers()->latest()->get(),
            ]
        );
    }

    public function profileFollowingRaw(User $user) {
        return response()->json([
            'theHTML' => view(
                'profile-following-only',
                ['following' => $user->followingTheseUsers()->latest()->get()]
            )->render(),
            'docTitle' => 'Who ' . $user->username . " follows"
        ]);
    }

    public function logout() {
        event(new OurExampleEvent([
            'username' => auth()->user()->username,
            'action' => 'logout'
        ]));
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');;
    }

    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view(
                'homepage-feed',
                [
                    'posts' => auth()->user()->feedPosts()->latest()->paginate(4)
                ]
            );
        } else {
            if (Cache::has('postCount')) {
                $postCount = Cache::get('postCount');
            } else {
                $postCount = Cache::remember('postCount', 20, function() {
                    return Post::count();
                });
            }
            return view(
                'homepage',
                [
                    'postCount' => $postCount
                ]
            );
        }
    }

    public function loginApi(Request $request) {
        $incomingFields = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (auth()->attempt([
            'username' => $incomingFields['username'],
            'password' => $incomingFields['password'],
        ])) {
            $user = User::where('username', $incomingFields['username'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        }

        return '';
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required',
        ]);

        if (auth()->attempt([
            'username' => $incomingFields['loginusername'],
            'password' => $incomingFields['loginpassword'],
        ])) {
            $request->session()->regenerate();
            event(new OurExampleEvent([
                'username' => auth()->user()->username,
                'action' => 'login'
            ]));
            return redirect('/')->with('success', 'You have successfully logged in.');
        } else {
            return redirect('/')->with('failure', 'Invalid login.');;
        }
    }

    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => [
                'required',
                'min:3',
                'max:20',
                Rule::unique('users', 'username')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'min:8',
                'confirmed'
            ],
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);
        auth()->login($user);

        return redirect('/')->with('success', 'Thank you for creating an account.');
    }
}

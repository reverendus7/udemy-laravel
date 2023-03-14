<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
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
                'avatar' => $user->avatar,
                'currentlyFollowing' => $currentlyFollowing
            ]
        );
    }

    public function profile(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-post',
            [
                'posts' => $user->posts()->latest()->get(),
            ]
        );
    }

    public function profileFollowers(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-followers',
            [
                'posts' => $user->posts()->latest()->get(),
            ]
        );
    }

    public function profileFollowing(User $user) {
        $this->getSharedProfileData($user);
        return view(
            'profile-following',
            [
                'posts' => $user->posts()->latest()->get(),
            ]
        );
    }

    public function logout() {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');;
    }

    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('homepage');
        }
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

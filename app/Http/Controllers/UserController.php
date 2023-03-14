<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function profile(User $user) {
        return view(
            'profile-post',
            [
                'username' => $user->username,
                'posts' => $user->posts()->latest()->get(),
                'postCount' => $user->posts()->count()
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

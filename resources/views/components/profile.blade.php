<x-layout :doctitle="$doctitle">
    <div class="container py-md-5 container--narrow">
        <h2>
            <img class="avatar-small" src="{{$sharedProfileData['avatar']}}" /> {{$sharedProfileData['username']}}
            @auth
            @if(!$sharedProfileData['currentlyFollowing'] AND auth()->user()->username != $sharedProfileData['username'])
                <form class="ml-2 d-inline" action="/create-follow/{{$sharedProfileData['username']}}" method="POST">
                    @csrf
                    <button class="btn btn-primary btn-sm">Follow <i class="fas fa-user-plus"></i></button>
                </form>
            @endif

            @if($sharedProfileData['currentlyFollowing'])
                <form class="ml-2 d-inline" action="/remove-follow/{{$sharedProfileData['username']}}" method="POST">
                    @csrf
                    <button class="btn btn-danger btn-sm">Stop Following <i class="fas fa-user-times"></i></button>
                </form>
            @endif

            @if(auth()->user()->username == $sharedProfileData['username'])
                <a href="/manage-avatar" class="btn btn-secondary btn-sm">Manage Avatar</a>
            @endif
            @endauth
        </h2>

        <div class="profile-nav nav nav-tabs pt-2 mb-4">
            <a href="/profile/{{$sharedProfileData['username']}}" class="profile-nav-link nav-item nav-link {{Request::segment(3) == '' ? 'active' : ''}}">Posts: {{$sharedProfileData['postCount']}}</a>
            <a href="/profile/{{$sharedProfileData['username']}}/followers" class="profile-nav-link nav-item nav-link {{Request::segment(3) == 'followers' ? 'active' : ''}}">Followers: {{$sharedProfileData['followersCount']}}</a>
            <a href="/profile/{{$sharedProfileData['username']}}/following" class="profile-nav-link nav-item nav-link {{Request::segment(3) == 'following' ? 'active' : ''}}">Following: {{$sharedProfileData['followingCount']}}</a>
        </div>

        <div class="profile-slot-content">
            {{$slot}}
        </div>
    </div>
</x-layout>

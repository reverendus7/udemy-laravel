<x-profile :sharedProfileData="$sharedProfileData" doctitle="{{$sharedProfileData['username']}}'s Followers">
    @include('profile-followers-only')
</x-profile>

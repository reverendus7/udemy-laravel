<x-profile :sharedProfileData="$sharedProfileData" doctitle="{{$sharedProfileData['username']}}'s Follows">
    @include('profile-following-only')
</x-profile>

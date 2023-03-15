<x-profile :sharedProfileData="$sharedProfileData" doctitle="{{$sharedProfileData['username']}}'s Profile">
    @include('profile-posts-only')
</x-profile>

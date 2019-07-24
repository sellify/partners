<dropdown-trigger class="h-9 flex items-center" slot-scope="{toggle}" :handle-click="toggle">
    @isset($user->email)
        <img
            src="https://secure.gravatar.com/avatar/{{ md5($user->email) }}?size=512"
            class="rounded-full w-8 h-8 mr-3"
        />
    @endisset

    <span class="text-90">
        {{ $user->name ?? $user->email ?? __('Nova User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ url(Nova::path() . '/resources/users/' . $user->id) }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('View Profile') }}
            </a>

            <a href="{{ url(Nova::path() . '/resources/users/' . $user->id . '/edit') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Edit Profile') }}
            </a>

            <a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</dropdown-menu>

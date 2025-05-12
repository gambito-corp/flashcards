<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-[99] header-mbs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo con margen y padding a la izquierda -->
            <div class="shrink-0 flex mr-24 pr-24 site-logo">
                <a href="{{ route('dashboard') }}">
                    <x-application-mark class="block h-9"/>
                </a>
            </div>

            <!-- Enlaces de navegación -->
            <div class="flex">
                <!-- Enlaces de navegación para escritorio -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @foreach ($menu as $item)
                        <x-nav-link href="{{ route($item['route']) }}" :active="request()->routeIs($item['route'])">
                            {{ __($item['name']) }}
                            {{--                                @if(!auth()->user()->hasAnyRole('admin', 'root', 'colab', 'Rector'))--}}
                            {{--                                    @if(($item['need_premium'] === true && Auth::user()->status == 0))--}}
                            {{--                                        <span class="ml-1 inline-block bg-yellow-400 text-xs text-white px-1 rounded">PRO</span>--}}
                            {{--                                    @endif--}}
                            {{--                                @endif--}}
                        </x-nav-link>
                    @endforeach
                </div>

            </div>

            <!-- Menú de equipos y configuración (para escritorio) -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        {{ Auth::user()->currentTeam ? Auth::user()->currentTeam->name : 'Selecciona Materia' }}
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>
                            <x-slot name="content">
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Selecciona Materia') }}
                                </div>
                                @forelse (Auth::user()->teams as $team)
                                    <x-dropdown-link href="{{ route('current-team.updates', $team) }}"
                                                     onclick="event.preventDefault(); document.getElementById('team-switch-form-{{ $team->id }}').submit();">
                                        {{ $team->name }}
                                    </x-dropdown-link>
                                    <form id="team-switch-form-{{ $team->id }}"
                                          action="{{ route('current-team.updates', $team) }}" method="POST"
                                          class="hidden">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                @empty
                                    <x-dropdown-link href="#">
                                        {{ __('No hay materias') }}
                                    </x-dropdown-link>
                                @endforelse
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    @if(Auth::user()->profile_photo_path)
                                        <img class="h-8 w-8 rounded-full object-cover"
                                             src="{{ Storage::disk('s3')->temporaryUrl(Auth::user()->profile_photo_path, now()->addMinutes(10)) }}"
                                             alt="{{ Auth::user()->name }}">
                                    @else
                                        <img class="h-8 w-8 rounded-full object-cover"
                                             src="{{ Auth::user()->profile_photo_url }}"
                                             alt="{{ Auth::user()->name }}"/>
                                    @endif
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>
                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>
                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <div class="border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}"
                                                 @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger para vistas móviles -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Responsive -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1 menu-movil">
            @foreach ($menu as $item)
                <x-responsive-nav-link href="{{ route($item['route']) }}" :active="request()->routeIs($item['route'])">
                    {{ __($item['name']) }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
            <div x-data="{ teamOpen: false }" class="pt-2 pb-3 space-y-1 z-50 ">
                <button @click="teamOpen = !teamOpen"
                        class="z-50 w-full flex justify-between items-center px-4 py-2 text-xs text-gray-400 focus:outline-none">
                    <span>
                        {{ Auth::user()->currentTeam ? Auth::user()->currentTeam->name : __('Selecciona Materia') }}
                    </span>
                    <svg class="ml-2 h-4 w-4 transform" :class="{'rotate-180': teamOpen}"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="teamOpen" class="z-50 mt-2 space-y-1">
                    @forelse (Auth::user()->teams as $team)
                        <x-responsive-nav-link href="{{ route('current-team.updates', $team) }}"
                                               onclick="event.preventDefault(); document.getElementById('team-switch-form-mobile-{{ $team->id }}').submit();">
                            {{ $team->name }}
                        </x-responsive-nav-link>
                        <form id="team-switch-form-mobile-{{ $team->id }}"
                              action="{{ route('current-team.updates', $team) }}" method="POST" class="hidden">
                            @csrf
                            @method('PUT')
                        </form>
                    @empty
                        <x-responsive-nav-link href="#">
                            {{ __('No hay materias') }}
                        </x-responsive-nav-link>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}"
                             alt="{{ Auth::user()->name }}"/>
                    </div>
                @endif
                <div class="ml-3">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                                           @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

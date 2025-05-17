<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">

            <div class="shrink-0 flex mr-24 pr-24 pt-8">
                <a href="{{ route('dashboard') }}">
                    <x-application-mark class="block h-9"/>
                </a>
            </div>
        </x-slot>

        <x-validation-errors class="mb-4"/>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.custom') }}">
            @csrf
            <p class="m-25 text-sm">Ingresa con tu cuenta personal para guardar tu progreso individual y obtener un
                análisis personalizado de tu rendimiento.</p>
            <div>
                <x-label for="email" value="{{ __('Email') }}"/>
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                         autofocus autocomplete="username"/>
            </div>

            <div class="mt-4">
                <div class="relative">
                    <!-- Campo de Contraseña -->
                    <x-label for="password" value="{{ __('Password') }}"/>
                    <x-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required
                             autocomplete="current-password"/>

                    <!-- Icono de Ojo -->
                    <span id="togglePassword">
        <!-- Aquí puedes usar cualquier icono, en este caso, Font Awesome -->
        <i class="fas fa-eye absolute right-3 cursor-pointer primary-color bottom-[15px]" aria-hidden="true"></i>
    </span>
                </div>

            </div>
            <a href="{{route('auth.redirect', 'facebook' )}}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-[#1877f3] hover:bg-[#145db2] text-white font-semibold rounded-md shadow transition duration-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        d="M22.675 0h-21.35C.595 0 0 .595 0 1.326v21.348C0 23.405.595 24 1.326 24h11.495v-9.294H9.691v-3.622h3.13V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24h-1.918c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.405 24 24 23.405 24 22.674V1.326C24 .595 23.405 0 22.675 0"/>
                </svg>
                Iniciar sesión con Facebook
            </a>


            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember"/>
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4 pt-6">

                <a href="{{ route('register') }}"
                   class="ml-4 rounded-md border border-[#0d3a54] text-[#0d3a54] bg-white hover:bg-[#0d3a54] hover:text-white font-bold py-2 px-4 text-xs transition-colors duration-200">
                    Regístrate ahora
                </a>

                <x-button class="ml-4 button-primary hover:bg-[#0d3a54]">
                    {{ __('Log in') }}
                </x-button>
            </div>
            <br>
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md "
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </form>
    </x-authentication-card>
</x-guest-layout>


<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        // Cambiar tipo de input entre 'password' y 'text'
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>


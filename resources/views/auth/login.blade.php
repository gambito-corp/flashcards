<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">

            <div class="shrink-0 flex mr-24 pr-24 pt-8">
                <a href="{{ route('dashboard') }}">
                    <x-application-mark class="block h-9" />
                </a>
            </div>
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.custom') }}">
            @csrf
        <p class="m-25 text-sm">Ingresa con tu cuenta personal para guardar tu progreso individual y obtener un análisis personalizado de tu rendimiento.</p>
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
            <div class="relative">
    <!-- Campo de Contraseña -->
    <x-label for="password" value="{{ __('Password') }}" />
    <x-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required autocomplete="current-password" />

    <!-- Icono de Ojo -->
    <span id="togglePassword">
        <!-- Aquí puedes usar cualquier icono, en este caso, Font Awesome -->
        <i class="fas fa-eye absolute right-3 cursor-pointer primary-color bottom-[15px]" aria-hidden="true"></i>
    </span>
</div>
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4 pt-6">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md " href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ml-4 button-primary ">
                    {{ __('Log in') }}
                </x-button>

                
            </div>
            <a href="{{ route('register') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md text-center block pt-6">¿No tienes una cuenta? Regístrate ahora</a>
        </form>
    </x-authentication-card>
</x-guest-layout>





<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
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


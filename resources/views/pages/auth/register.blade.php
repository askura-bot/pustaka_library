<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-3xl font-black uppercase tracking-tight mb-2">Create Account</h1>
            <p class="text-zinc-600 font-medium">Join us to start your Research Journey.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <div class="flex flex-col gap-2">
                <label for="name" class="font-bold text-lg">Full Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Full name" class="neo-input" />
                @error('name') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- Email Address -->
            <div class="flex flex-col gap-2">
                <label for="email" class="font-bold text-lg">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="email@example.com" class="neo-input" />
                @error('email') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <label for="password" class="font-bold text-lg">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Password" class="neo-input" />
                @error('password') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="font-bold text-lg">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Confirm password" class="neo-input" />
                @error('password_confirmation') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-col gap-4 mt-2">
                <button type="submit" class="neo-btn neo-btn-green w-full">
                    Create Account
                </button>
                
                <a href="{{ route('auth.google') }}" class="neo-btn neo-btn-white w-full flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                    Continue with Google
                </a>
            </div>
        </form>

        <div class="text-center font-bold mt-4">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" class="underline hover:text-neo-purple" wire:navigate>Log in</a>
        </div>
    </div>
</x-layouts::auth>

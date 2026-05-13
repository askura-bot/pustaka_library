<div class="flex items-start max-md:flex-col gap-8">
    {{-- Settings Navigation --}}
    <div class="w-full md:w-[220px] shrink-0">
        <nav class="flex md:flex-col gap-2">
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="px-4 py-2 font-bold text-sm border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] {{ request()->routeIs('profile.edit') ? 'bg-neo-purple text-white' : 'bg-white text-black' }}">
                👤 {{ __('Profile') }}
            </a>
            <a href="{{ route('security.edit') }}" wire:navigate
               class="px-4 py-2 font-bold text-sm border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] {{ request()->routeIs('security.edit') ? 'bg-neo-purple text-white' : 'bg-white text-black' }}">
                🔒 {{ __('Security') }}
            </a>
        </nav>
    </div>

    {{-- Settings Content --}}
    <div class="flex-1 self-stretch">
        <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] p-6 md:p-8">
            <h2 class="text-2xl font-black uppercase mb-1">{{ $heading ?? '' }}</h2>
            <p class="text-zinc-500 font-medium text-sm mb-6">{{ $subheading ?? '' }}</p>

            <div class="w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

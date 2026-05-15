<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neo-offwhite text-black" x-data="{ mobileMenuOpen: false }">

        {{-- TOP NAVIGATION (Sticky, Neubrutalism) --}}
        <header class="sticky top-0 z-50 bg-white border-b-4 border-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">

                    {{-- Left: Logo + Nav Links --}}
                    <div class="flex items-center gap-6">
                        {{-- Logo --}}
                        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 shrink-0">
                            <span class="bg-neo-purple text-white border-2 border-black px-2 py-1 text-sm font-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">📚</span>
                            <span class="text-xl font-black uppercase tracking-tighter">
                                Digi<span class="text-neo-purple">Lib</span>
                            </span>
                        </a>

                        {{-- Desktop Nav Links --}}
                        <nav class="hidden md:flex items-center gap-1">
                            <a href="{{ route('dashboard') }}" wire:navigate
                               class="px-3 py-1.5 text-sm font-bold uppercase tracking-wider transition-all border-2 border-transparent hover:border-black hover:bg-neo-yellow {{ request()->routeIs('dashboard') ? 'bg-neo-yellow border-black!' : '' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('library.templates') }}" wire:navigate
                               class="px-3 py-1.5 text-sm font-bold uppercase tracking-wider transition-all border-2 border-transparent hover:border-black hover:bg-neo-lilac {{ request()->routeIs('library.templates') ? 'bg-neo-lilac border-black!' : '' }}">
                                Template
                            </a>
                            <a href="{{ route('library.ask-ai') }}" wire:navigate
                               class="px-3 py-1.5 text-sm font-bold uppercase tracking-wider transition-all border-2 border-transparent hover:border-black hover:bg-neo-green {{ request()->routeIs('library.ask-ai') ? 'bg-neo-green border-black!' : '' }}">
                                🤖 Ask AI
                            </a>
                        </nav>
                    </div>

                    {{-- Right: User Menu (Desktop) --}}
                    <div class="hidden md:flex items-center gap-3">
                        <a href="{{ route('profile.edit') }}" wire:navigate
                           class="border-2 border-black bg-neo-lilac px-3 py-1.5 text-sm font-bold shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                            <span class="bg-neo-purple text-white w-6 h-6 flex items-center justify-center text-xs font-black border-2 border-black">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span class="max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="border-2 border-black bg-red-500 text-white px-3 py-1.5 text-sm font-bold shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] transition-all"
                                    data-test="logout-button">
                                Logout
                            </button>
                        </form>
                    </div>

                    {{-- Mobile: Hamburger Button --}}
                    <button x-on:click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden border-2 border-black bg-white p-2 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] transition-all">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu (Slide-down) --}}
            <div x-show="mobileMenuOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t-4 border-black bg-white">
                <nav class="flex flex-col p-4 gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate x-on:click="mobileMenuOpen = false"
                       class="px-4 py-3 font-bold uppercase text-sm border-2 border-black {{ request()->routeIs('dashboard') ? 'bg-neo-yellow' : 'bg-white' }} shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                        📋 Dashboard
                    </a>
                    <a href="{{ route('library.templates') }}" wire:navigate x-on:click="mobileMenuOpen = false"
                       class="px-4 py-3 font-bold uppercase text-sm border-2 border-black {{ request()->routeIs('library.templates') ? 'bg-neo-lilac' : 'bg-white' }} shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                        📄 Template KTI
                    </a>
                    <a href="{{ route('library.ask-ai') }}" wire:navigate x-on:click="mobileMenuOpen = false"
                       class="px-4 py-3 font-bold uppercase text-sm border-2 border-black {{ request()->routeIs('library.ask-ai') ? 'bg-neo-green' : 'bg-white' }} shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                        🤖 Ask AI
                    </a>
                    <a href="{{ route('profile.edit') }}" wire:navigate x-on:click="mobileMenuOpen = false"
                       class="px-4 py-3 font-bold uppercase text-sm border-2 border-black bg-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                        ⚙️ Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-3 font-bold uppercase text-sm border-2 border-black bg-red-500 text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] text-left">
                            🚪 Logout
                        </button>
                    </form>
                </nav>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            {{ $slot }}
        </main>

        {{-- Global Ask AI Sticky Button (FAB) — hidden on article detail, ask-ai, and folder pages --}}
        @auth
            @if(!request()->routeIs('library.ask-ai') && !request()->routeIs('library.article') && !request()->routeIs('library.folder'))
                <a href="{{ route('library.ask-ai') }}"
                   wire:navigate
                   class="fixed bottom-6 right-6 z-50 bg-neo-green text-black neo-border p-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all group"
                   title="Ask AI — Research Assistant">
                    <span class="text-2xl">🤖</span>
                    <span class="absolute bottom-full right-0 mb-2 bg-black text-white text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity border-2 border-black">
                        Ask AI
                    </span>
                </a>
            @endif
        @endauth

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

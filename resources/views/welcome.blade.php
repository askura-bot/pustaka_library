<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Library - Research Command Center</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,900" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fafafa] text-[#171717] font-sans antialiased overflow-x-hidden selection:bg-neo-yellow selection:text-black">
    <div class="min-h-screen flex flex-col">
        
        <!-- Navbar -->
        <nav class="border-b-4 border-black bg-white p-4 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <a href="{{ url('/') }}" class="text-2xl font-black uppercase tracking-tighter">
                    Digi<span class="text-neo-purple">Lib</span>
                </a>
                
                <div class="flex gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="neo-btn neo-btn-white text-sm px-4 py-2">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="neo-btn neo-btn-white text-sm px-4 py-2">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="neo-btn neo-btn-yellow text-sm px-4 py-2 hidden sm:inline-flex">
                                    Sign up
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow flex flex-col justify-center items-center px-4 py-20 lg:py-32 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+CjxyZWN0IHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgZmlsbD0idHJhbnNwYXJlbnQiIC8+CjxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjEiIGZpbGw9IiNjY2MiIC8+Cjwvc3ZnPg==')]">
            <div class="max-w-5xl mx-auto text-center space-y-8">
                <div class="inline-block bg-neo-purple text-white px-4 py-1 neo-border font-bold uppercase tracking-widest text-sm mb-4 transform -rotate-2">
                    Level Up Your Research
                </div>
                
                <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[0.9]">
                    Research <br> Command <span class="text-neo-green bg-black px-4 pt-2 neo-shadow inline-block transform rotate-1">Center</span>
                </h1>
                
                <p class="text-xl sm:text-2xl font-medium max-w-2xl mx-auto mt-6 bg-white p-4 neo-border neo-shadow">
                    Perpustakaan digital masa depan dengan AI Analysis, Semantic Search, dan Template dinamis. Dibangun untuk mahasiswa Gen Z.
                </p>

                <div class="flex flex-col sm:flex-row justify-center items-center gap-6 mt-10">
                    <a href="{{ route('login') }}" class="neo-btn neo-btn-yellow text-xl px-10 py-5 w-full sm:w-auto transform hover:-rotate-2">
                        Mulai Riset Sekarang
                    </a>
                    <a href="#features" class="neo-btn neo-btn-white text-lg px-8 py-4 w-full sm:w-auto">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </main>

        <!-- Features Grid -->
        <section id="features" class="border-t-4 border-black bg-white py-24 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl sm:text-5xl font-black uppercase inline-block border-b-8 border-neo-purple pb-2">
                        Superpowers Untuk Risetmu
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-neo-yellow neo-border neo-shadow p-8 flex flex-col gap-4 transform hover:-translate-y-2 transition-transform">
                        <div class="bg-white neo-border p-4 w-16 h-16 flex items-center justify-center mb-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black uppercase">AI Analysis</h3>
                        <p class="font-medium text-lg leading-relaxed">
                            Pusing baca jurnal ratusan halaman? Biarkan AI kami yang menganalisis dan merangkum poin-poin penting untukmu dalam hitungan detik.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-neo-purple text-white neo-border neo-shadow p-8 flex flex-col gap-4 transform hover:-translate-y-2 transition-transform">
                        <div class="bg-white text-black neo-border p-4 w-16 h-16 flex items-center justify-center mb-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black uppercase">Semantic Search</h3>
                        <p class="font-medium text-lg leading-relaxed">
                            Cari bukan dari kata kunci yang pas, tapi dari makna. Temukan literatur yang paling relevan bahkan jika kamu lupa judulnya.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-neo-green neo-border neo-shadow p-8 flex flex-col gap-4 transform hover:-translate-y-2 transition-transform">
                        <div class="bg-white neo-border p-4 w-16 h-16 flex items-center justify-center mb-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black uppercase">Custom Template</h3>
                        <p class="font-medium text-lg leading-relaxed">
                            Tidak perlu lagi mengatur margin atau sitasi secara manual. Gunakan template siap pakai yang disesuaikan dengan standar kampusmu.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t-4 border-black bg-white py-8 px-4 text-center font-bold">
            <p>&copy; {{ date('Y') }} Digital Library. Built for Gen Z researchers.</p>
        </footer>
    </div>
</body>
</html>

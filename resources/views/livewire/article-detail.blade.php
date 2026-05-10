<div class="text-black dark:text-black">
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="neo-border bg-white text-black px-3 py-2 hover:bg-black hover:text-white transition-colors" title="Kembali">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="text-3xl font-black uppercase tracking-tight text-black dark:text-white truncate max-w-lg" title="{{ $article->file_name }}">
                {{ $article->file_name }}
            </h2>
        </div>
        <div class="bg-black text-white neo-border px-3 py-1 font-bold text-sm">
            {{ $article->ktiType->name ?? 'Dokumen' }}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Kiri: Info & Status / View Dokumen Asli -->
        <div class="flex flex-col gap-6">
            <div class="bg-white neo-border neo-shadow p-6 text-black">
                <h3 class="text-2xl font-black uppercase mb-4 border-b-4 border-black pb-2">Informasi File</h3>
                <div class="grid grid-cols-2 gap-4 font-medium">
                    <div class="flex flex-col">
                        <span class="text-zinc-500 text-sm font-bold uppercase">Nama File</span>
                        <span class="break-words">{{ $article->file_name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-zinc-500 text-sm font-bold uppercase">Tipe File</span>
                        <span class="uppercase font-black">{{ $article->file_type }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-zinc-500 text-sm font-bold uppercase">Tanggal Unggah</span>
                        <span>{{ $article->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-zinc-500 text-sm font-bold uppercase">Status</span>
                        @if($article->status === 'completed')
                            <span class="text-green-600 font-black uppercase">Berhasil Dianalisis</span>
                        @elseif($article->status === 'failed')
                            <span class="text-red-600 font-black uppercase">Gagal</span>
                        @else
                            <span class="text-yellow-600 font-black uppercase">Dalam Proses</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="bg-neo-purple text-white neo-border neo-shadow p-6 flex-grow min-h-[300px] flex flex-col items-center justify-center text-center">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <p class="font-bold text-lg">Pratinjau Dokumen Asli</p>
                <p class="text-sm opacity-80 mt-2">Pratinjau langsung untuk file dari private storage belum dikonfigurasi.</p>
                <button class="mt-4 bg-white text-black neo-border px-4 py-2 font-bold hover:bg-black hover:text-white transition-colors">Unduh File Asli</button>
            </div>
        </div>

        <!-- Kanan: Hasil AI dengan POLLING -->
        <div class="flex flex-col h-full" 
             @if(in_array($article->status, ['pending', 'processing'])) wire:poll.3s @endif>
            
            @if(in_array($article->status, ['pending', 'processing']))
                <!-- Loading State AI -->
                <div class="bg-neo-yellow text-black neo-border neo-shadow p-12 flex-grow flex flex-col items-center justify-center text-center animate-pulse">
                    <div class="bg-black text-white neo-border p-6 mb-6 shadow-[8px_8px_0px_0px_rgba(255,255,255,1)] transform -rotate-3">
                        <svg class="w-16 h-16 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-4xl font-black uppercase mb-4 tracking-tight">AI Sedang Membedah Dokumen...</h3>
                    <p class="text-xl font-medium max-w-md">Gemini sedang membaca, memahami, dan mengekstrak informasi penting dari jurnalmu. Tunggu sebentar ya!</p>
                </div>
            @elseif($article->status === 'failed')
                <!-- Error State AI -->
                <div class="bg-red-500 text-white neo-border neo-shadow p-12 flex-grow flex flex-col items-center justify-center text-center">
                    <div class="bg-white text-red-600 neo-border p-6 mb-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transform rotate-3">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-4xl font-black uppercase mb-4 tracking-tight">Waduh, AI Lagi Capek.</h3>
                    <p class="text-xl font-medium max-w-md mb-6">Proses analisis gagal. Bisa jadi dokumen korup, API kena limit panjang, atau ada masalah teknis lainnya. Coba lagi nanti ya!</p>
                    <button wire:click="reanalyze" wire:loading.attr="disabled" class="neo-btn bg-white text-black text-xl py-4 px-8 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
                        <span wire:loading.remove wire:target="reanalyze">Coba Analisis Lagi 🔄</span>
                        <span wire:loading wire:target="reanalyze">Memproses...</span>
                    </button>
                </div>
            @elseif($article->status === 'completed' && $article->analysis_results)
                <!-- Sukses State AI (JSON Results) -->
                <div class="bg-neo-green text-black neo-border neo-shadow p-8 flex-grow flex flex-col overflow-y-auto max-h-[800px]">
                    <div class="flex items-center gap-3 mb-6 border-b-4 border-black pb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <h3 class="text-3xl font-black uppercase tracking-tight">Hasil Bedah AI</h3>
                    </div>

                    <div class="flex flex-col gap-6">
                        @foreach($article->analysis_results as $key => $value)
                            <div class="bg-white neo-border p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                <h4 class="text-xl font-black uppercase mb-2 text-neo-purple">{{ str_replace('_', ' ', $key) }}</h4>
                                <div class="font-medium text-lg text-zinc-800 whitespace-pre-wrap leading-relaxed">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

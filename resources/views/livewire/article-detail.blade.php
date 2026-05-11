<div class="text-black dark:text-black">
    {{-- Navigation Bar --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}"
               class="neo-border bg-neo-yellow text-black px-4 py-2 font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                ← Kembali ke Library
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-black text-white neo-border px-3 py-1 font-bold text-sm uppercase">
                {{ $article->ktiType->name ?? 'Dokumen' }}
            </span>
            @if($article->status === 'completed')
                <span class="bg-neo-green text-black neo-border px-3 py-1 font-bold text-sm uppercase">✓ Selesai</span>
            @elseif($article->status === 'failed')
                <span class="bg-red-500 text-white neo-border px-3 py-1 font-bold text-sm uppercase">✗ Gagal</span>
            @else
                <span class="bg-neo-yellow text-black neo-border px-3 py-1 font-bold text-sm uppercase animate-pulse">⏳ Proses</span>
            @endif
        </div>
    </div>

    {{-- Title --}}
    <h1 class="text-2xl lg:text-3xl font-black uppercase tracking-tight text-black dark:text-white mb-6 truncate" title="{{ $article->file_name }}">
        {{ $article->title ?? $article->file_name }}
    </h1>

    {{-- Split Screen Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- KIRI: Document Viewer --}}
        <div class="flex flex-col gap-6">
            <div class="bg-white neo-border shadow-neo min-h-[600px] lg:min-h-[750px] flex flex-col">
                <div class="bg-black text-white px-4 py-3 font-bold uppercase text-sm flex items-center justify-between">
                    <span>📄 Dokumen Asli</span>
                    <span class="text-xs opacity-70 uppercase">{{ $article->file_type }}</span>
                </div>

                @if($article->file_type === 'pdf')
                    <iframe
                        src="{{ route('library.article.file', $article) }}"
                        class="w-full grow border-0"
                        style="min-height: 700px;"
                        title="PDF Viewer - {{ $article->file_name }}">
                    </iframe>
                @else
                    <div class="grow flex flex-col items-center justify-center p-8 text-center bg-zinc-50">
                        <div class="bg-neo-purple text-white neo-border p-6 mb-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transform -rotate-3">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="font-bold text-lg mb-2">File DOCX tidak bisa ditampilkan langsung</p>
                        <p class="text-zinc-500 mb-4">Unduh file untuk membacanya di aplikasi Word.</p>
                        <a href="{{ route('library.article.file', $article) }}"
                           download="{{ $article->file_name }}"
                           class="neo-btn neo-btn-purple shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            Unduh File
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- KANAN: Analysis Hub --}}
        <div class="flex flex-col gap-5 overflow-y-auto max-h-[900px] lg:max-h-none"
             @if(in_array($article->status, ['pending', 'processing'])) wire:poll.3s @endif>

            @if(in_array($article->status, ['pending', 'processing']))
                {{-- SKELETON LOADER --}}
                <div class="bg-neo-yellow text-black neo-border shadow-neo p-8 flex flex-col items-center justify-center text-center min-h-[400px]">
                    <div class="bg-black text-white neo-border p-6 mb-6 shadow-[8px_8px_0px_0px_rgba(255,255,255,1)] transform -rotate-3">
                        <svg class="w-16 h-16 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black uppercase mb-4 tracking-tight">AI LAGI MEMBEDAH ISI FILE...</h3>
                    <p class="text-lg font-medium max-w-md">Gemini sedang membaca dan mengekstrak informasi penting. Tunggu sebentar ya!</p>
                    <div class="w-full mt-8 flex flex-col gap-3">
                        @for($i = 0; $i < 5; $i++)
                            <div class="h-6 bg-black/10 neo-border animate-pulse" style="width: {{ rand(60, 100) }}%"></div>
                        @endfor
                        <div class="h-20 bg-black/10 neo-border animate-pulse mt-2"></div>
                    </div>
                </div>

            @elseif($article->status === 'failed')
                {{-- ERROR STATE --}}
                <div class="bg-red-500 text-white neo-border shadow-neo p-8 flex flex-col items-center justify-center text-center min-h-[400px]">
                    <div class="bg-white text-red-600 neo-border p-6 mb-6 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transform rotate-3">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black uppercase mb-4 tracking-tight">Analisis Gagal</h3>
                    <p class="text-lg font-medium max-w-md mb-6">Bisa jadi dokumen korup, API kena limit, atau ada masalah teknis. Coba lagi!</p>
                    <button wire:click="reanalyze" wire:loading.attr="disabled"
                            class="neo-btn bg-white text-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
                        <span wire:loading.remove wire:target="reanalyze">Coba Analisis Lagi 🔄</span>
                        <span wire:loading wire:target="reanalyze">Memproses...</span>
                    </button>
                </div>

            @elseif($article->status === 'completed' && $article->analysis_results)
                @php
                    $results = $article->analysis_results;
                @endphp

                {{-- 1. ABSTRAK --}}
                @if(isset($results['abstract']))
                    <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] p-5">
                        <h3 class="text-lg font-black uppercase mb-2 text-neo-purple flex items-center gap-2">
                            <span class="bg-neo-purple text-white w-7 h-7 flex items-center justify-center neo-border text-xs">1</span>
                            Abstrak
                        </h3>
                        <p class="text-zinc-800 leading-relaxed font-medium text-sm">{{ $results['abstract'] }}</p>
                    </div>
                @endif

                {{-- 2-6. TABEL ANALISIS DINAMIS (5 kolom spesifik) --}}
                @if(count($dynamicColumns) > 0 && collect($dynamicColumns)->filter()->isNotEmpty())
                    <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
                        <div class="bg-neo-yellow px-5 py-3 border-b-4 border-black">
                            <h3 class="text-lg font-black uppercase flex items-center gap-2">
                                <span class="bg-black text-white w-7 h-7 flex items-center justify-center neo-border text-xs">📊</span>
                                Hasil Ekstraksi ({{ count($dynamicColumns) }} Poin)
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-neo-yellow/40">
                                        <th class="border-b-4 border-r-4 border-black px-4 py-2 text-left font-black uppercase text-xs w-1/3">Kolom</th>
                                        <th class="border-b-4 border-black px-4 py-2 text-left font-black uppercase text-xs">Hasil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dynamicColumns as $columnName => $columnValue)
                                        <tr class="border-b-2 border-black/15 last:border-b-0 hover:bg-neo-yellow/5 transition-colors">
                                            <td class="border-r-4 border-black px-4 py-3 font-bold uppercase text-xs bg-zinc-50">
                                                {{ $columnName }}
                                            </td>
                                            <td class="px-4 py-3 font-medium text-zinc-800 text-sm leading-relaxed">
                                                @if(is_array($columnValue))
                                                    <ul class="list-disc list-inside space-y-1">
                                                        @foreach($columnValue as $item)
                                                            <li>{{ is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                @elseif($columnValue)
                                                    {{ $columnValue }}
                                                @else
                                                    <span class="text-zinc-400 italic">Tidak tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- 7. SO WHAT? --}}
                @if(isset($results['so_what']))
                    <div class="bg-neo-purple text-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] p-5">
                        <h3 class="text-lg font-black uppercase mb-2 flex items-center gap-2">
                            <span class="bg-white text-black w-7 h-7 flex items-center justify-center neo-border text-xs">7</span>
                            So What?
                        </h3>
                        <p class="leading-relaxed font-medium text-white/95 text-sm">{{ $results['so_what'] }}</p>
                    </div>
                @endif

                {{-- 8. KESIMPULAN --}}
                @if(isset($results['conclusion']))
                    <div class="bg-neo-green text-black neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] p-5">
                        <h3 class="text-lg font-black uppercase mb-2 flex items-center gap-2">
                            <span class="bg-black text-white w-7 h-7 flex items-center justify-center neo-border text-xs">8</span>
                            Kesimpulan
                        </h3>
                        <p class="leading-relaxed font-medium text-sm">{{ $results['conclusion'] }}</p>
                    </div>
                @endif

                {{-- EXTRA RESULTS (non-template, non-reserved) --}}
                @php
                    $templateCols = $article->ktiType->columns ?? [];
                    $reservedKeys = array_merge($templateCols, ['abstract', 'so_what', 'conclusion', 'title', 'author', 'year']);
                    $extraResults = collect($results)->except($reservedKeys)->filter();
                @endphp

                @if($extraResults->isNotEmpty())
                    <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] p-5">
                        <h3 class="text-lg font-black uppercase mb-3 text-neo-purple flex items-center gap-2">
                            <span class="bg-neo-green text-black w-7 h-7 flex items-center justify-center neo-border text-xs">💡</span>
                            Temuan Tambahan
                        </h3>
                        <div class="flex flex-col gap-3">
                            @foreach($extraResults as $key => $value)
                                <div class="bg-zinc-50 neo-border p-3">
                                    <h4 class="font-bold uppercase text-xs text-zinc-600 mb-1">{{ str_replace('_', ' ', $key) }}</h4>
                                    <div class="text-zinc-800 font-medium text-sm leading-relaxed">
                                        @if(is_array($value))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($value as $item)
                                                    <li>{{ is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ACADEMIC CITATION & BIBLIOGRAPHY --}}
                <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]"
                     x-data="{ activeTab: 'intext', copiedIntext: false, copiedBib: false, copiedAiCitation: false, copiedAiBib: false }">

                    <div class="bg-neo-yellow border-b-4 border-black px-5 py-3">
                        <h3 class="text-lg font-black uppercase flex items-center gap-2">
                            <span class="bg-black text-white w-7 h-7 flex items-center justify-center neo-border text-xs">📚</span>
                            Citation & Bibliography
                        </h3>
                    </div>

                    {{-- AI-POWERED GENERATE REFERENCE --}}
                    <div class="p-5">
                        <h4 class="font-black uppercase text-xs mb-2 flex items-center gap-2">
                            <span class="text-base">🤖</span> AI Reference Generator
                        </h4>

                        <div class="flex flex-col sm:flex-row gap-3 mb-4">
                            <select wire:model="referenceStyle" class="neo-input bg-white text-black font-bold grow text-sm">
                                <option value="apa">APA</option>
                                <option value="mla">MLA</option>
                                <option value="ieee">IEEE</option>
                                <option value="harvard">Harvard</option>
                            </select>

                            @if($article->citation_output)
                                {{-- Sudah ada output → tampilkan Regenerate --}}
                                <button wire:click="generateReference"
                                        wire:loading.attr="disabled"
                                        wire:target="generateReference"
                                        class="neo-btn bg-black text-white shadow-[4px_4px_0px_0px_rgba(168,85,247,1)] whitespace-nowrap text-sm hover:shadow-[2px_2px_0px_0px_rgba(168,85,247,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all">
                                    <span wire:loading.remove wire:target="generateReference">🔄 Regenerate</span>
                                    <span wire:loading wire:target="generateReference" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Generating...
                                    </span>
                                </button>
                            @else
                                {{-- Belum ada output → tampilkan Generate --}}
                                <button wire:click="generateReference"
                                        wire:loading.attr="disabled"
                                        wire:target="generateReference"
                                        class="neo-btn neo-btn-purple shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap text-sm">
                                    <span wire:loading.remove wire:target="generateReference">⚡ Generate</span>
                                    <span wire:loading wire:target="generateReference" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span class="animate-pulse">AI sedang bekerja...</span>
                                    </span>
                                </button>
                            @endif
                        </div>

                        @if($generateError)
                            <div class="bg-red-100 neo-border p-3 text-red-700 font-bold text-xs mb-4">
                                {{ $generateError }}
                            </div>
                        @endif

                        {{-- AI Output (hanya tampil jika sudah ada) --}}
                        @if($article->citation_output || $article->bibliography_output)
                            <div class="flex flex-col gap-3">
                                @if($article->citation_output)
                                    <div class="bg-neo-green/15 neo-border p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-black uppercase text-xs text-zinc-700">🤖 In-Text Citation</span>
                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText($refs.aiCitationText.innerText); copiedAiCitation = true; setTimeout(() => copiedAiCitation = false, 2000);"
                                                class="neo-btn neo-btn-green text-xs px-3 py-1 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]"
                                                :class="copiedAiCitation ? 'translate-x-[3px] translate-y-[3px] shadow-none bg-black text-white' : ''">
                                                <span x-show="!copiedAiCitation">📋 Copy</span>
                                                <span x-show="copiedAiCitation" x-cloak>✓</span>
                                            </button>
                                        </div>
                                        <div class="bg-white neo-border p-3 font-mono text-xs leading-relaxed" x-ref="aiCitationText">{{ $article->citation_output }}</div>
                                    </div>
                                @endif

                                @if($article->bibliography_output)
                                    <div class="bg-neo-purple/10 neo-border p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-black uppercase text-xs text-zinc-700">🤖 Bibliography</span>
                                            <button type="button"
                                                x-on:click="navigator.clipboard.writeText($refs.aiBibText.innerText); copiedAiBib = true; setTimeout(() => copiedAiBib = false, 2000);"
                                                class="neo-btn neo-btn-green text-xs px-3 py-1 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]"
                                                :class="copiedAiBib ? 'translate-x-[3px] translate-y-[3px] shadow-none bg-black text-white' : ''">
                                                <span x-show="!copiedAiBib">📋 Copy</span>
                                                <span x-show="copiedAiBib" x-cloak>✓</span>
                                            </button>
                                        </div>
                                        <div class="bg-white neo-border p-3 font-mono text-xs leading-relaxed" x-ref="aiBibText">{{ $article->bibliography_output }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            @else
                <div class="bg-zinc-100 neo-border shadow-neo p-8 flex flex-col items-center justify-center text-center min-h-[300px]">
                    <p class="text-xl font-bold text-zinc-500">Belum ada hasil analisis.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="text-black dark:text-black flex flex-col h-[calc(100vh-120px)]"
     x-data="{ }"
     x-init="$nextTick(() => { if ($refs.chatScroll) $refs.chatScroll.scrollTop = $refs.chatScroll.scrollHeight })"
     @chat-updated.window="$nextTick(() => { if ($refs.chatScroll) $refs.chatScroll.scrollTop = $refs.chatScroll.scrollHeight })">

    {{-- Header --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h2 class="text-3xl font-black uppercase tracking-tight text-black dark:text-white flex items-center gap-3">
                <span class="bg-neo-green text-black neo-border p-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transform -rotate-3">🤖</span>
                Ask AI — Research Assistant
            </h2>
            <p class="text-zinc-600 dark:text-zinc-400 font-medium mt-1">Tanya apa saja tentang seluruh koleksi pustakamu. AI akan mencari referensi yang relevan.</p>
        </div>
        <a href="{{ route('dashboard') }}"
           class="neo-border bg-neo-yellow text-black px-4 py-2 font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] text-sm whitespace-nowrap">
            ← Library
        </a>
    </div>

    {{-- Chat Container --}}
    <div class="bg-white neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex flex-col grow overflow-hidden">

        {{-- Chat Messages --}}
        <div class="flex flex-col gap-4 p-5 overflow-y-auto grow" x-ref="chatScroll">
            @if(count($chatHistory) === 0)
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center text-center py-16 text-zinc-400 grow">
                    <div class="bg-neo-green text-black neo-border p-6 mb-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transform rotate-2">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black uppercase mb-2 text-black">Ask Across Library</h3>
                    <p class="font-medium text-sm max-w-md text-zinc-500">
                        Tanya apa saja! AI akan mencari artikel yang relevan di pustakamu dan menjawab berdasarkan data yang ada.
                    </p>
                    <div class="flex flex-wrap gap-2 mt-6 justify-center max-w-lg">
                        <span class="bg-neo-green/30 text-black neo-border px-3 py-1 text-xs font-bold">💡 "Apa metode yang paling sering digunakan?"</span>
                        <span class="bg-neo-green/30 text-black neo-border px-3 py-1 text-xs font-bold">💡 "Rangkum temuan tentang NLP"</span>
                        <span class="bg-neo-green/30 text-black neo-border px-3 py-1 text-xs font-bold">💡 "Bandingkan pendekatan di artikel-artikelku"</span>
                    </div>
                </div>
            @else
                @foreach($chatHistory as $chat)
                    {{-- User Message --}}
                    <div class="flex justify-end" wire:key="gchat-{{ $chat->id }}-user">
                        <div class="bg-neo-yellow text-black neo-border p-4 max-w-[80%] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <p class="font-medium leading-relaxed">{{ $chat->message }}</p>
                            <span class="text-xs opacity-60 font-bold mt-2 block text-right">{{ $chat->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    {{-- AI Response --}}
                    <div class="flex justify-start" wire:key="gchat-{{ $chat->id }}-ai">
                        <div class="bg-neo-green/20 text-black neo-border p-4 max-w-[80%] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-neo-green text-black neo-border px-2 py-0.5 text-xs font-black uppercase">🤖 AI</span>
                                @if(isset($chat->metadata['source_count']) && $chat->metadata['source_count'] > 0)
                                    <span class="text-xs font-bold text-zinc-500">{{ $chat->metadata['source_count'] }} sumber</span>
                                @endif
                            </div>
                            <div class="font-medium leading-relaxed whitespace-pre-wrap text-sm">{{ $chat->response }}</div>
                            <span class="text-xs opacity-60 font-bold mt-2 block">{{ $chat->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Typing indicator --}}
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="bg-neo-green/40 text-black neo-border p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-black uppercase">🤖 AI sedang mencari & menjawab</span>
                        <span class="flex gap-1">
                            <span class="w-2.5 h-2.5 bg-black rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-2.5 h-2.5 bg-black rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2.5 h-2.5 bg-black rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chat Error --}}
        @if($chatError)
            <div class="mx-5 mb-3 bg-red-100 neo-border p-3 text-red-700 font-bold text-sm">
                {{ $chatError }}
            </div>
        @endif

        {{-- Chat Input --}}
        <div class="border-t-4 border-black p-5 bg-zinc-50">
            <form wire:submit="sendMessage" class="flex gap-3">
                <input wire:model="message"
                       type="text"
                       placeholder="Tanya sesuatu tentang koleksi pustakamu..."
                       class="neo-input grow bg-white text-black font-medium"
                       wire:loading.attr="disabled"
                       wire:target="sendMessage"
                       autocomplete="off" />
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                        class="neo-btn neo-btn-green shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] px-6 shrink-0">
                    <span wire:loading.remove wire:target="sendMessage" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim
                    </span>
                    <span wire:loading wire:target="sendMessage" class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Mencari...
                    </span>
                </button>
            </form>
            @error('message') <span class="text-red-500 font-bold text-xs mt-2 block">{{ $message }}</span> @enderror
            <p class="text-xs text-zinc-400 font-medium mt-2">AI mencari referensi dari artikelmu yang paling relevan, lalu menjawab berdasarkan data tersebut.</p>
        </div>
    </div>
</div>

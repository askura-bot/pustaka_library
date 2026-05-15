<div class="text-black">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}"
               class="neo-border bg-neo-yellow text-black px-4 py-2 font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                ← Dashboard
            </a>
        </div>
        <div class="flex gap-2">
            <button wire:click="$set('showUploadModal', true)"
                    class="neo-btn neo-btn-purple shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-sm">
                📄 Upload Baru
            </button>
            <button wire:click="$set('showAddArticleModal', true)"
                    class="neo-btn neo-btn-green shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-sm">
                + Tambah Artikel
            </button>
        </div>
    </div>

    {{-- Folder Info --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black uppercase tracking-tight flex items-center gap-3">
            <span class="bg-neo-purple text-white neo-border p-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">📁</span>
            {{ $folder->name }}
        </h1>
        @if($folder->description)
            <p class="text-zinc-500 font-medium mt-2">{{ $folder->description }}</p>
        @endif
        <p class="text-sm font-bold text-zinc-400 mt-1">{{ count($articles) }} artikel</p>
    </div>

    {{-- Articles in Folder --}}
    @if(count($articles) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($articles as $article)
                @php
                    $colors = ['bg-neo-yellow', 'bg-white', 'bg-neo-lilac', 'bg-neo-green'];
                    $colorClass = $colors[$loop->index % count($colors)];
                @endphp
                <div class="neo-border shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex flex-col {{ $colorClass }}">
                    <a href="{{ route('library.article', $article) }}" class="grow p-5 flex flex-col gap-3">
                        <div class="flex justify-between items-start">
                            <div class="bg-black text-white p-1.5 border-2 border-black">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <object>
                                <button wire:click.prevent="removeArticle({{ $article->id }})" class="bg-red-500 text-white border-2 border-black p-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all cursor-pointer" title="Keluarkan dari folder">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </object>
                        </div>
                        <h3 class="font-black text-base line-clamp-2 leading-tight">{{ $article->title ?? $article->file_name }}</h3>
                        <div class="mt-auto flex flex-wrap gap-1">
                            @if($article->keywords)
                                @foreach(array_slice($article->keywords, 0, 3) as $kw)
                                    <span class="bg-neo-yellow/80 text-black text-[10px] font-bold px-1.5 py-0.5 border-2 border-black">{{ $kw }}</span>
                                @endforeach
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-neo-lilac neo-border shadow-neo p-12 text-center">
            <h3 class="text-2xl font-black uppercase mb-2">Folder masih kosong</h3>
            <p class="text-zinc-600 font-medium">Tambahkan artikel ke folder ini.</p>
        </div>
    @endif

    {{-- Add Article Modal --}}
    @if($showAddArticleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('showAddArticleModal', false)">
            <div class="bg-white neo-border p-6 max-w-lg w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] max-h-[80vh] flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-black uppercase">Tambah Artikel ke Folder</h3>
                    <button wire:click="$set('showAddArticleModal', false)" class="bg-white text-black border-4 border-black p-1 hover:bg-black hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari artikel..." class="neo-input mb-4 text-sm" />

                <div class="overflow-y-auto grow flex flex-col gap-2">
                    @if(count($availableArticles) > 0)
                        @foreach($availableArticles as $article)
                            <div class="flex items-center justify-between gap-3 p-3 border-2 border-black bg-zinc-50 hover:bg-neo-yellow/20 transition-colors">
                                <div class="min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $article->title ?? $article->file_name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $article->ktiType->name ?? '' }}</p>
                                </div>
                                <button wire:click="addArticle({{ $article->id }})" class="bg-neo-green text-black border-2 border-black px-3 py-1 text-xs font-bold shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all shrink-0">
                                    + Add
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-zinc-400 text-sm font-medium text-center py-8">Tidak ada artikel yang tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Upload Modal (Direct to Folder) --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false; progress = 0"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
             wire:click.self="$set('showUploadModal', false)">

            <div class="bg-neo-yellow text-black neo-border p-8 max-w-lg w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-2xl font-black uppercase">Upload ke Folder</h3>
                    <button wire:click="$set('showUploadModal', false)" class="bg-white text-black border-4 border-black p-1 hover:bg-black hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-sm font-medium mb-4 bg-white neo-border p-2">
                    📁 Artikel akan otomatis masuk ke folder <strong>{{ $folder->name }}</strong>
                </p>

                <form wire:submit="uploadFile" class="flex flex-col gap-5">
                    <div>
                        <label class="font-bold text-sm block mb-2">Pilih Jenis KTI</label>
                        @if(count($ktiTypes) > 0)
                            <select wire:model="selectedKtiTypeId" class="neo-input bg-white text-black font-medium">
                                <option value="">-- Pilih Template --</option>
                                @foreach($ktiTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedKtiTypeId') <span class="text-red-600 font-bold text-xs mt-1 block">{{ $message }}</span> @enderror
                        @endif
                    </div>

                    <div>
                        <label class="font-bold text-sm block mb-2">File PDF / DOCX</label>
                        <div class="bg-white neo-border p-4 text-center border-dashed border-4 cursor-pointer relative">
                            <input type="file" wire:model="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx" />
                            <p class="font-bold text-sm text-zinc-500">Klik atau seret file ke sini (maks 10MB)</p>
                        </div>
                        @error('file') <span class="text-red-600 font-bold text-xs mt-1 block">{{ $message }}</span> @enderror
                        @if($file)
                            <div class="bg-neo-green neo-border p-2 mt-2 font-bold text-sm flex justify-between items-center">
                                <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="$set('file', null)" class="text-red-600 text-xs font-bold">✕</button>
                            </div>
                        @endif
                    </div>

                    <div x-show="uploading" class="w-full bg-white neo-border h-5 relative overflow-hidden" style="display: none;">
                        <div class="bg-neo-purple h-full absolute top-0 left-0 transition-all" :style="`width: ${progress}%`"></div>
                        <div class="absolute inset-0 flex items-center justify-center font-bold text-[10px]" x-text="`${progress}%`"></div>
                    </div>

                    <button type="submit" class="neo-btn neo-btn-purple w-full" wire:loading.attr="disabled" wire:target="uploadFile">
                        <span wire:loading.remove wire:target="uploadFile">Unggah & Analisis</span>
                        <span wire:loading wire:target="uploadFile">Memproses...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Folder Chat Sticky Button --}}
    <button wire:click="$toggle('showChat')"
            class="fixed bottom-6 right-6 z-40 bg-neo-purple text-white neo-border p-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all group"
            title="Chat tentang folder ini">
        <span class="text-2xl">💬</span>
        <span class="absolute bottom-full right-0 mb-2 bg-black text-white text-xs font-bold px-3 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity border-2 border-black">
            Chat Folder
        </span>
    </button>

    {{-- Folder Chat Panel (Slide-up) --}}
    @if($showChat)
        <div class="fixed bottom-24 right-6 z-40 w-[380px] max-h-[500px] flex flex-col bg-white neo-border shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]"
             x-data="{ }"
             x-init="$nextTick(() => { if ($refs.folderChatScroll) $refs.folderChatScroll.scrollTop = $refs.folderChatScroll.scrollHeight })"
             @folder-chat-updated.window="$nextTick(() => { if ($refs.folderChatScroll) $refs.folderChatScroll.scrollTop = $refs.folderChatScroll.scrollHeight })">

            {{-- Chat Header --}}
            <div class="bg-neo-purple text-white border-b-4 border-black px-4 py-3 flex items-center justify-between shrink-0">
                <h4 class="font-black uppercase text-sm flex items-center gap-2">
                    💬 Chat: {{ Str::limit($folder->name, 20) }}
                </h4>
                <button wire:click="$set('showChat', false)" class="text-white hover:text-neo-yellow transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Chat Messages --}}
            <div class="flex flex-col gap-2 p-3 overflow-y-auto grow min-h-[200px] max-h-[320px]" x-ref="folderChatScroll">
                @if(count($folderChatHistory) === 0)
                    <div class="flex flex-col items-center justify-center text-center py-6 text-zinc-400">
                        <p class="font-bold text-xs">Tanya AI tentang artikel di folder ini</p>
                        <p class="text-[10px] mt-1">AI akan menjawab berdasarkan semua artikel dalam folder.</p>
                    </div>
                @else
                    @foreach($folderChatHistory as $chat)
                        <div class="flex justify-end" wire:key="fchat-{{ $chat->id }}-u">
                            <div class="bg-neo-yellow text-black border-2 border-black p-2 max-w-[80%] shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                <p class="text-xs font-medium leading-relaxed">{{ $chat->message }}</p>
                            </div>
                        </div>
                        <div class="flex justify-start" wire:key="fchat-{{ $chat->id }}-a">
                            <div class="bg-neo-lilac text-black border-2 border-black p-2 max-w-[80%] shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                <p class="text-xs font-medium leading-relaxed whitespace-pre-wrap">{{ $chat->response }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif

                <div wire:loading wire:target="sendChatMessage" class="flex justify-start">
                    <div class="bg-neo-lilac border-2 border-black p-2 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="flex gap-1 items-center">
                            <span class="text-[10px] font-bold">AI mengetik</span>
                            <span class="w-1.5 h-1.5 bg-black rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-1.5 h-1.5 bg-black rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-1.5 h-1.5 bg-black rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Chat Error --}}
            @if($chatError)
                <div class="mx-3 mb-2 bg-red-100 border-2 border-black p-2 text-red-700 font-bold text-[10px]">{{ $chatError }}</div>
            @endif

            {{-- Chat Input --}}
            <div class="border-t-4 border-black p-3 shrink-0">
                <form wire:submit="sendChatMessage" class="flex gap-2">
                    <input wire:model="chatMessage" type="text" placeholder="Tanya tentang folder ini..."
                           class="neo-input grow text-xs py-2 bg-zinc-50"
                           wire:loading.attr="disabled" wire:target="sendChatMessage" autocomplete="off" />
                    <button type="submit" wire:loading.attr="disabled" wire:target="sendChatMessage"
                            class="bg-neo-purple text-white border-2 border-black px-3 py-2 font-bold shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all shrink-0">
                        <span wire:loading.remove wire:target="sendChatMessage">➤</span>
                        <span wire:loading wire:target="sendChatMessage" class="animate-spin inline-block">⟳</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

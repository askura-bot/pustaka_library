<div class="text-black">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
        <div>
            <h2 class="text-4xl font-black uppercase tracking-tight mb-2 text-black">Pustaka Dokumen</h2>
            <p class="text-zinc-600 font-medium">Kelola dan upload karya tulis ilmiah Anda untuk dianalisis oleh AI.</p>
        </div>
        <button wire:click="$set('showUploadModal', true)" class="neo-btn neo-btn-green w-full md:w-auto shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-none">
            + Tambah Dokumen
        </button>
    </div>

    <!-- Smart Search Bar -->
    <div class="mb-6">
        <div class="relative">
            <div class="flex gap-3 items-center">
                <div class="relative grow">
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari judul, penulis, kata kunci, atau isi analisis..."
                           class="neo-input font-medium" />
                </div>
                <button type="button" class="neo-btn neo-btn-purple shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] px-4 py-3 shrink-0" title="Smart Search">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
            @if(trim($search) !== '')
                <div class="mt-2 text-xs font-bold text-zinc-500">
                    Menampilkan {{ count($articles) }} hasil untuk "<span class="text-neo-purple">{{ $search }}</span>"
                </div>
            @endif
        </div>
    </div>

    <!-- Folders Section -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-black uppercase tracking-tight">📁 Folder</h3>
            <button wire:click="openFolderModal" class="neo-btn neo-btn-lilac text-sm shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                + Folder Baru
            </button>
        </div>

        @if(count($folders) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($folders as $folder)
                    <div class="neo-border bg-neo-lilac p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
                        <a href="{{ route('library.folder', $folder) }}" class="block">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-black text-lg leading-tight">{{ $folder->name }}</h4>
                                    @if($folder->description)
                                        <p class="text-xs text-zinc-600 mt-1">{{ $folder->description }}</p>
                                    @endif
                                </div>
                                <span class="bg-neo-purple text-white text-xs font-bold px-2 py-1 border-2 border-black shrink-0">
                                    {{ $folder->articles_count }}
                                </span>
                            </div>
                        </a>
                        <div class="flex gap-2 mt-3">
                            <button wire:click="openFolderModal({{ $folder->id }})" class="text-xs font-bold bg-neo-yellow border-2 border-black px-2 py-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                                ✏️ Edit
                            </button>
                            <button wire:click="deleteFolder({{ $folder->id }})" wire:confirm="Hapus folder ini? Artikel di dalamnya tetap aman di Dashboard." class="text-xs font-bold bg-red-500 text-white border-2 border-black px-2 py-1 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all">
                                🗑️
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-zinc-400 font-medium text-sm">Belum ada folder. Buat folder untuk mengelompokkan artikelmu.</p>
        @endif
    </div>

    <!-- Library Grid -->
    @if(count($articles) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" wire:poll.5s>
            @foreach($articles as $index => $article)
                @php
                    // Pilih warna background berdasarkan index tipe kti untuk variasi
                    $colors = ['bg-neo-yellow', 'bg-white', 'bg-neo-purple text-white', 'bg-neo-green'];
                    $colorClass = $colors[$article->kti_type_id % count($colors)];
                    
                    // Format tanggal
                    $date = \Carbon\Carbon::parse($article->created_at)->translatedFormat('d M Y');
                @endphp
                <div class="neo-border neo-shadow flex flex-col transform transition-transform hover:-translate-y-2 {{ $colorClass }} {{ str_contains($colorClass, 'bg-neo-purple') ? 'text-white' : 'text-black' }}">
                    
                    <a href="{{ route('library.article', $article->id) }}" class="grow p-5 flex flex-col gap-4">
                        <div class="flex justify-between items-start gap-2">
                            <!-- Icon PDF/DOCX -->
                            <div class="bg-black text-white p-2 border-2 border-black">
                                @if($article->file_type === 'pdf')
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-6-3v6"></path></svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @endif
                            </div>
                            <!-- Delete button (prevent default so it doesn't trigger link) -->
                            <object>
                                <button wire:click.prevent="confirmDelete({{ $article->id }})" class="bg-red-500 text-white border-2 border-black p-1.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-none transition-all flex-shrink-0 cursor-pointer relative z-10" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </object>
                        </div>

                        <div class="mt-2 flex-grow">
                            <h3 class="font-black text-lg line-clamp-2 leading-tight" title="{{ $article->file_name }}">
                                {{ $article->file_name }}
                            </h3>
                            <p class="text-sm font-medium mt-1 opacity-80">{{ $date }}</p>
                        </div>

                        <div class="mt-auto">
                            <span class="inline-block bg-black text-white text-xs font-bold px-2 py-1 uppercase tracking-wider neo-border">
                                {{ $article->ktiType->name }}
                            </span>
                            
                            @if(in_array($article->status, ['pending', 'processing']))
                                <span class="inline-block bg-yellow-300 text-black text-xs font-bold px-2 py-1 uppercase tracking-wider neo-border ml-1 mt-2 animate-pulse">
                                    AI Menganalisis...
                                </span>
                            @elseif($article->status === 'failed')
                                <span class="inline-block bg-red-600 text-white text-xs font-bold px-2 py-1 uppercase tracking-wider neo-border ml-1 mt-2">
                                    Gagal
                                </span>
                            @else
                                <span class="inline-block bg-green-500 text-white text-xs font-bold px-2 py-1 uppercase tracking-wider neo-border ml-1 mt-2">
                                    Selesai
                                </span>
                            @endif

                            {{-- Keywords tags (max 3) --}}
                            @if($article->keywords && count($article->keywords) > 0)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach(array_slice($article->keywords, 0, 3) as $keyword)
                                        <span class="inline-block bg-neo-yellow/80 text-black text-[10px] font-bold px-1.5 py-0.5 border-2 border-black">
                                            {{ $keyword }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Not Linked Badge --}}
                            @if($article->folders->isEmpty())
                                <span class="inline-block bg-zinc-200 text-zinc-600 text-[10px] font-bold px-1.5 py-0.5 border-2 border-zinc-400 mt-2">
                                    📂 not linked folder
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-neo-purple text-white neo-border neo-shadow p-12 flex flex-col items-center justify-center text-center h-full min-h-[400px]">
            <div class="bg-neo-yellow text-black neo-border p-6 mb-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transform rotate-3">
                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            </div>
            <h3 class="text-4xl font-black uppercase mb-4">Pustakamu masih kosong!</h3>
            <p class="text-xl font-medium max-w-lg mb-8 text-white/90">Yuk, gas upload jurnal atau skripsi pertamamu buat mulai dianalisis otomatis pake AI!</p>
            <button wire:click="$set('showUploadModal', true)" class="neo-btn neo-btn-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] text-xl py-4 px-8 hover:translate-x-1 hover:translate-y-1 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                Upload Sekarang 🔥
            </button>
        </div>
    @endif

    <!-- Upload Modal -->
    <div x-data="{ uploading: false, progress: 0 }" 
         x-on:livewire-upload-start="uploading = true"
         x-on:livewire-upload-finish="uploading = false; progress = 0"
         x-on:livewire-upload-error="uploading = false"
         x-on:livewire-upload-progress="progress = $event.detail.progress"
         x-show="$wire.showUploadModal" 
         style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
         x-transition.opacity>
         
        <div class="bg-neo-yellow text-black neo-border p-8 max-w-lg w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]" 
             x-show="$wire.showUploadModal" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 translate-y-8" 
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="$wire.set('showUploadModal', false)">
            
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-3xl font-black uppercase">Tambah Dokumen</h3>
                <button wire:click="$set('showUploadModal', false)" class="bg-white text-black border-4 border-black p-1 hover:bg-black hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form wire:submit="uploadFile" class="flex flex-col gap-6">
                <!-- Select KTI Type -->
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-lg">Pilih Jenis KTI</label>
                    @if(count($ktiTypes) > 0)
                        <select wire:model="selectedKtiTypeId" class="neo-input bg-white text-black font-medium">
                            <option value="">-- Pilih Jenis Template --</option>
                            @foreach($ktiTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedKtiTypeId') <span class="text-red-600 font-bold bg-white px-1 inline-block">{{ $message }}</span> @enderror
                    @else
                        <div class="bg-white p-3 neo-border text-red-600 font-bold">
                            Anda belum membuat Template KTI. Silakan buat di menu Template terlebih dahulu.
                        </div>
                    @endif
                </div>

                <!-- File Input -->
                <div class="flex flex-col gap-2 relative">
                    <label class="font-bold text-lg">Upload File PDF / DOCX</label>
                    <div class="bg-white neo-border p-6 text-center border-dashed border-4 cursor-pointer hover:bg-zinc-50 transition-colors relative" 
                         x-data="{ isDragging: false }" 
                         @dragover.prevent="isDragging = true" 
                         @dragleave.prevent="isDragging = false" 
                         @drop.prevent="isDragging = false"
                         :class="{ 'border-blue-500 bg-blue-50': isDragging }">
                        
                        <input type="file" wire:model="file" id="file-upload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx" />
                        
                        <div class="pointer-events-none flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <div>
                                <span class="font-bold underline text-blue-600">Klik untuk browse</span> atau seret file ke sini
                                <p class="text-sm text-zinc-500 mt-1 font-medium">Maksimal 10MB (.pdf, .docx)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div wire:loading wire:target="file" class="font-bold text-blue-600">
                        Menyiapkan file...
                    </div>
                    @error('file') <span class="text-red-600 font-bold bg-white px-1 inline-block">{{ $message }}</span> @enderror

                    <!-- Preview Selected File -->
                    @if($file)
                        <div class="bg-neo-green neo-border p-3 font-bold flex justify-between items-center">
                            <span class="truncate pr-4">{{ $file->getClientOriginalName() }}</span>
                            <button type="button" wire:click="$set('file', null)" class="text-red-600 hover:text-red-800">Batal</button>
                        </div>
                    @endif
                </div>

                <!-- Progress Bar -->
                <div x-show="uploading" class="w-full bg-white neo-border h-6 relative overflow-hidden" style="display: none;">
                    <div class="bg-blue-600 h-full neo-border !border-y-0 !border-l-0 absolute top-0 left-0 transition-all duration-300" :style="`width: ${progress}%`"></div>
                    <div class="absolute inset-0 flex items-center justify-center font-bold text-xs" :class="progress > 50 ? 'text-white' : 'text-black'" x-text="`${progress}% Mengunggah...`"></div>
                </div>

                <div class="mt-4">
                    <button type="submit" 
                            class="neo-btn neo-btn-purple w-full text-xl py-4"
                            wire:loading.attr="disabled"
                            wire:target="uploadFile"
                            @if(count($ktiTypes) == 0) disabled @endif>
                        <span wire:loading.remove wire:target="uploadFile">Unggah Dokumen</span>
                        <span wire:loading wire:target="uploadFile">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="$wire.showDeleteModal" 
         style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
         x-transition.opacity>
         
        <div class="bg-white text-black neo-border p-8 max-w-md w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] text-center" 
             x-show="$wire.showDeleteModal" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100"
             @click.outside="$wire.cancelDelete()">
            
            <div class="bg-red-500 text-white w-20 h-20 mx-auto neo-border flex items-center justify-center mb-6 transform -rotate-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            
            <h3 class="text-3xl font-black uppercase mb-3">Hapus Dokumen?</h3>
            <p class="text-zinc-600 font-medium mb-4">Tindakan ini tidak bisa dibatalkan. File dan hasil analisis akan dihapus secara permanen.</p>

            @if(count($articleFolderNames) > 0)
                <div class="bg-neo-yellow neo-border p-3 mb-6 text-left">
                    <p class="font-bold text-sm mb-1">⚠️ Artikel ini tertaut di folder:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($articleFolderNames as $fname)
                            <span class="bg-neo-purple text-white text-xs font-bold px-2 py-0.5 border-2 border-black">{{ $fname }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-700 mt-2 font-medium">Menghapus dari sini akan menghapusnya secara permanen dari semua folder tersebut.</p>
                </div>
            @endif
            
            <div class="flex flex-col sm:flex-row gap-4">
                <button wire:click="cancelDelete" class="neo-btn bg-zinc-200 text-black flex-1">
                    Batal
                </button>
                <button wire:click="deleteArticle" class="neo-btn bg-red-500 text-white flex-1">
                    Ya, Hapus!
                </button>
            </div>
        </div>
    </div>

    <!-- Folder Create/Edit Modal -->
    @if($showFolderModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('showFolderModal', false)">
            <div class="bg-white neo-border p-8 max-w-md w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-2xl font-black uppercase">{{ $editingFolderId ? 'Edit Folder' : 'Folder Baru' }}</h3>
                    <button wire:click="$set('showFolderModal', false)" class="bg-white text-black border-4 border-black p-1 hover:bg-black hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveFolder" class="flex flex-col gap-4">
                    <div>
                        <label class="font-bold text-sm block mb-2">Nama Folder</label>
                        <input wire:model="folderName" type="text" placeholder="Misal: Jurnal NLP" class="neo-input" required />
                        @error('folderName') <span class="text-red-500 font-bold text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-sm block mb-2">Deskripsi (opsional)</label>
                        <input wire:model="folderDescription" type="text" placeholder="Deskripsi singkat..." class="neo-input" />
                    </div>
                    <button type="submit" class="neo-btn neo-btn-purple w-full mt-2">
                        {{ $editingFolderId ? 'Update' : 'Buat Folder' }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

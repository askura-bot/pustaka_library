<div>
    <div class="mb-8">
        <h2 class="text-4xl font-black uppercase tracking-tight mb-2 text-black dark:text-white">Template Manager</h2>
        <p class="text-zinc-600 dark:text-zinc-400 font-medium">Buat dan kelola jenis KTI beserta kolom analisisnya.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white neo-border neo-shadow p-6 text-black">
                <h3 class="text-2xl font-black uppercase mb-6">{{ $editingId ? 'Edit Template' : 'Template Baru' }}</h3>
                
                <form wire:submit="save" class="flex flex-col gap-6">
                    <!-- Template Name -->
                    <div class="flex flex-col gap-2">
                        <label for="name" class="font-bold text-lg">Nama Kategori KTI</label>
                        <input wire:model="name" type="text" id="name" placeholder="Misal: Jurnal Internasional" class="neo-input text-black placeholder-zinc-500" />
                        @error('name') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <hr class="border-2 border-black" />

                    <!-- Columns Repeater -->
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-center">
                            <label class="font-bold text-lg">Kolom Analisis</label>
                            <button type="button" wire:click="addColumn" class="bg-neo-green text-black neo-border shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] px-3 py-1 font-bold text-sm hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                                + Tambah
                            </button>
                        </div>
                        
                        @error('columns') <span class="text-red-500 font-bold">{{ $message }}</span> @enderror

                        <div class="flex flex-col gap-3">
                            @foreach($columns as $index => $column)
                                @php
                                    $isProtected = in_array(trim($column), $protectedColumns, true);
                                    $colors = ['bg-neo-yellow! text-black!', 'bg-neo-purple! text-white!', 'bg-neo-green! text-black!'];
                                    $colorClass = $isProtected
                                        ? 'bg-zinc-100! text-black! font-bold'
                                        : $colors[$index % count($colors)];
                                @endphp
                                <div class="flex gap-2 items-center transition-all opacity-100 starting:opacity-0 motion-safe:starting:-translate-x-4" wire:key="column-{{ $index }}">
                                    <input wire:model="columns.{{ $index }}" type="text" placeholder="Nama Kolom {{ $index + 1 }} (Cth: Metode)" class="neo-input grow {{ $colorClass }}" @if($isProtected) readonly @endif />
                                    @if($isProtected)
                                        <div class="bg-zinc-300 text-zinc-500 neo-border p-3 shrink-0 cursor-not-allowed" title="Kolom wajib, tidak bisa dihapus">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </div>
                                    @else
                                        <button type="button" wire:click="removeColumn({{ $index }})" class="bg-red-500 text-white neo-border p-3 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all shrink-0" title="Hapus Kolom">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    @endif
                                </div>
                                @error('columns.'.$index) <span class="text-red-500 font-bold text-sm">{{ $message }}</span> @enderror
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3 mt-4">
                        <button type="submit" class="neo-btn neo-btn-purple w-full py-4 text-lg">
                            {{ $editingId ? 'Update Template' : 'Simpan Template' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit" class="neo-btn neo-btn-white w-full py-3">
                                Batal
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- List Section -->
        <div class="lg:col-span-2">
            @if(count($ktiTypes) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($ktiTypes as $type)
                        <div class="bg-white text-black neo-border neo-shadow p-6 flex flex-col gap-4 transform transition-transform hover:-translate-y-1">
                            <div class="flex justify-between items-start gap-2">
                                <h4 class="text-2xl font-black uppercase leading-tight">{{ $type->name }}</h4>
                                <div class="flex gap-2 flex-shrink-0">
                                    <button wire:click="edit({{ $type->id }})" class="bg-neo-yellow text-black neo-border p-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @if($type->name !== 'Article')
                                        <button wire:click="delete({{ $type->id }})" wire:confirm="Yakin ingin menghapus template ini?" class="bg-red-500 text-white neo-border p-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @else
                                        <div class="bg-zinc-200 text-zinc-400 neo-border p-2 cursor-not-allowed" title="Template default tidak bisa dihapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-auto">
                                @foreach($type->columns as $col)
                                    @php
                                        $isProtectedCol = $type->name === 'Article' && in_array($col, \App\Models\KtiType::ARTICLE_PROTECTED_COLUMNS, true);
                                    @endphp
                                    <span class="border-2 border-black px-3 py-1 text-sm font-bold text-black {{ $isProtectedCol ? 'bg-neo-yellow' : 'bg-gray-100' }}">
                                        {{ $col }}{{ $isProtectedCol ? ' 🔒' : '' }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="text-sm font-bold mt-2 text-zinc-500">
                                {{ count($type->columns) }} Kolom
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-neo-yellow text-black neo-border neo-shadow p-12 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                    <div class="bg-white text-black neo-border p-6 mb-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transform -rotate-3">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black uppercase mb-4">Belum ada template?</h3>
                    <p class="text-xl font-medium max-w-md">Yuk buat satu buat mulai riset! Tentukan kolom analisis sesuai kebutuhan penelitianmu.</p>
                </div>
            @endif
        </div>
    </div>
</div>

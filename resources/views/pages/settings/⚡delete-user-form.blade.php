<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="mt-10 space-y-4">
    <div class="border-t-4 border-black pt-6">
        <h3 class="text-xl font-black uppercase mb-1">{{ __('Danger Zone') }}</h3>
        <p class="text-zinc-500 font-medium text-sm mb-4">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <livewire:pages::settings.delete-user-modal />
</section>

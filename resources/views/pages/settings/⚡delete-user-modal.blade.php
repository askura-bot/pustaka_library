<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    public bool $showModal = false;

    /**
     * Check if the current user is an OAuth-only user (no local password).
     */
    public function isOAuthUser(): bool
    {
        $user = Auth::user();

        return $user->google_id && empty($user->password);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        if (! $this->isOAuthUser()) {
            $this->validate([
                'password' => $this->currentPasswordRules(),
            ]);
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

{{-- Custom Neubrutalism Modal --}}
<div>
    {{-- Trigger --}}
    <button wire:click="$set('showModal', true)"
            class="neo-btn bg-red-500 text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"
            data-test="delete-user-button">
        🗑️ {{ __('Delete account') }}
    </button>

    {{-- Modal Overlay --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             wire:click.self="$set('showModal', false)">

            {{-- Modal Content (Neubrutalism) --}}
            <div class="bg-white neo-border p-8 max-w-md w-full shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                {{-- Warning Icon --}}
                <div class="bg-red-500 text-white w-16 h-16 mx-auto neo-border flex items-center justify-center mb-6 transform -rotate-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-2xl font-black uppercase text-center mb-3">{{ __('Delete Account?') }}</h3>
                <p class="text-zinc-600 font-medium text-center mb-6 text-sm">
                    {{ __('Once deleted, all your data will be permanently removed.') }}
                    @if(! $this->isOAuthUser())
                        {{ __('Enter your password to confirm.') }}
                    @endif
                </p>

                <form wire:submit="deleteUser" class="space-y-4">
                    @if(! $this->isOAuthUser())
                        <div>
                            <label class="font-bold text-sm block mb-2">{{ __('Password') }}</label>
                            <input wire:model="password" type="password" class="neo-input" required />
                            @error('password') <span class="text-red-500 font-bold text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="neo-btn bg-zinc-200 text-black flex-1 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="neo-btn bg-red-500 text-white flex-1 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"
                                data-test="confirm-delete-user-button">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

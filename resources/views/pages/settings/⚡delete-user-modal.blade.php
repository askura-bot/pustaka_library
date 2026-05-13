<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

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
        // OAuth users don't need password confirmation
        if (! $this->isOAuthUser()) {
            $this->validate([
                'password' => $this->currentPasswordRules(),
            ]);
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

            <flux:subheading>
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
                @if(! $this->isOAuthUser())
                    {{ __('Please enter your password to confirm you would like to permanently delete your account.') }}
                @endif
            </flux:subheading>
        </div>

        @if(! $this->isOAuthUser())
            <flux:input wire:model="password" :label="__('Password')" type="password" viewable />
        @endif

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                {{ __('Delete account') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

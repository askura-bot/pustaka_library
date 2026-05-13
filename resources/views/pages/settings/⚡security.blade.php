<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Check if the current user is an OAuth-only user (no local password).
     */
    public function isOAuthUser(): bool
    {
        return auth()->user()->google_id && empty(auth()->user()->password);
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            if ($this->isOAuthUser()) {
                $validated = $this->validate([
                    'password' => $this->passwordRules(),
                ]);
            } else {
                $validated = $this->validate([
                    'current_password' => $this->currentPasswordRules(),
                    'password' => $this->passwordRules(),
                ]);
            }
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="$this->isOAuthUser() ? __('Set Password') : __('Update Password')" :subheading="$this->isOAuthUser() ? __('Set a password for your account (you logged in via Google)') : __('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            @if(! $this->isOAuthUser())
                <flux:input
                    wire:model="current_password"
                    :label="__('Current password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    viewable
                />
            @endif
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-password-button">
                    {{ $this->isOAuthUser() ? __('Set password') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>

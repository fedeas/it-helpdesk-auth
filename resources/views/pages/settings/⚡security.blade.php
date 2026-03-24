<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ρυθμίσεις Ασφάλειας')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Ρυθμίσεις Ασφάλειας</flux:heading>

    <x-pages::settings.layout :heading="'Αλλαγή κωδικού πρόσβασης'" :subheading="'Χρησιμοποιήστε έναν ισχυρό και μοναδικό κωδικό για μεγαλύτερη ασφάλεια'">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                label="Τρέχων κωδικός"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />

            <flux:input
                wire:model="password"
                label="Νέος κωδικός"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <flux:input
                wire:model="password_confirmation"
                label="Επιβεβαίωση νέου κωδικού"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-password-button">
                        Αποθήκευση
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Αποθηκεύτηκε.
                </x-action-message>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
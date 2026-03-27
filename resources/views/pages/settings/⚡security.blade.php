<x-layouts.app :title="'Ασφάλεια Λογαριασμού'">
    <x-settings.layout :heading="'Ασφάλεια'" :subheading="'Αλλάξτε τον κωδικό πρόσβασής σας'">
        @if (session('status') === 'password-updated')
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 3500)"
                class="fixed right-4 top-4 z-[9999] w-full max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-xl dark:border-emerald-900 dark:bg-emerald-900/90 dark:text-emerald-300"
            >
                <div class="flex items-start justify-between gap-3">
                    <span>Ο κωδικός πρόσβασης ενημερώθηκε επιτυχώς.</span>

                    <button
                        type="button"
                        @click="show = false"
                        class="text-emerald-700 transition duration-150 ease-out transform hover:scale-125 hover:opacity-80 active:scale-90 dark:text-emerald-300"
                    >
                        ✕
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="mb-2 block text-sm font-medium">Τρέχων Κωδικός</label>
                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('current_password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Νέος Κωδικός</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium">Επιβεβαίωση Νέου Κωδικού</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
            </div>

            <div class="flex items-center justify-end">
                <button
                    type="submit"
                    class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-150 ease-out transform hover:scale-[1.03] hover:shadow-lg hover:brightness-110 active:scale-[0.95] active:shadow-sm dark:bg-white dark:text-zinc-900"
                >
                    Αλλαγή Κωδικού
                </button>
            </div>
        </form>
    </x-settings.layout>
</x-layouts.app>
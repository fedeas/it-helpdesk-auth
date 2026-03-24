<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $status = '';
    public string $search = '';

    public bool $showDeleteModal = false;
    public ?int $ticketToDelete = null;

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $ticketId): void
    {
        $this->ticketToDelete = $ticketId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->ticketToDelete = null;
    }

    public function deleteTicket(): void
    {
        if (! $this->ticketToDelete) {
            return;
        }

        $ticket = Ticket::where('customer_id', auth()->id())
            ->findOrFail($this->ticketToDelete);

        $ticket->delete();

        $this->showDeleteModal = false;
        $this->ticketToDelete = null;

        session()->flash('success', 'Το δελτίο διαγράφηκε επιτυχώς.');
    }

    public function with(): array
    {
        return [
            'tickets' => auth()->user()
                ->tickets()
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
        ];
    }
};

?>

<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Τα Δελτία Μου</h1>
            <p class="text-sm text-zinc-500">Παρακολούθηση όλων των δελτίων βλάβης.</p>
        </div>

        <a href="{{ route('customer.tickets.create') }}" wire:navigate
           class="inline-flex items-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:opacity-90 dark:bg-white dark:text-zinc-900">
            Νέο Δελτίο
        </a>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="md:col-span-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Αναζήτηση με θέμα..."
                   class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900">
        </div>

        <div>
            <select wire:model.live="status"
                    class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900">
                <option value="">Όλες οι Καταστάσεις</option>
                <option value="backlog">Σε Εκκρεμότητα</option>
                <option value="in_progress">Σε Εξέλιξη</option>
                <option value="done">Ολοκληρωμένο</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θεμα</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Κατασταση</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Ωρα Αναγγελιας</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενεργειες</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-4 font-medium">{{ $ticket->title }}</td>
                        <td class="px-4 py-4"><x-status-badge :status="$ticket->status" /></td>
                        <td class="px-4 py-4 text-sm text-zinc-500">{{ optional($ticket->opened_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-4">
                            <div class="flex justify-end gap-2">
                                <a
                                    href="{{ route('customer.tickets.show', $ticket) }}"
                                    wire:navigate
                                    class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                >
                                    Προβολή
                                </a>

                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $ticket->id }})"
                                    class="inline-flex items-center rounded-lg border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/20"
                                >
                                    Διαγραφή
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500">Δεν βρέθηκαν δελτία.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $tickets->links() }}
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">Διαγραφή Δελτίου</h2>
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                    Αυτή η ενέργεια είναι μόνιμη και θα αφαιρέσει το δελτίο από την ενεργή προβολή.
                    Θέλετε να συνεχίσετε;
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium dark:border-zinc-700"
                    >
                        Όχι
                    </button>

                    <button
                        type="button"
                        wire:click="deleteTicket"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Ναι, διαγραφή
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
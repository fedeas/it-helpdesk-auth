<?php

use App\Models\Ticket;
use Livewire\Component;

new class extends Component {
    public Ticket $ticket;
    public bool $showDeleteModal = false;

    public function mount(Ticket $ticket): void
    {
        abort_unless($ticket->customer_id === auth()->id(), 403);

        $this->ticket = $ticket->load('updates.admin');
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteTicket(): mixed
    {
        abort_unless($this->ticket->customer_id === auth()->id(), 403);

        $this->ticket->delete();

        session()->flash('success', 'Το δελτίο διαγράφηκε επιτυχώς.');

        return $this->redirect(route('customer.tickets.index'), navigate: true);
    }

    public function back(): mixed
    {
        return $this->redirect(route('customer.dashboard'), navigate: true);
    }
};

?>

<div class="mx-auto max-w-6xl space-y-6">
    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Δελτίο Βλάβης</h1>
            <p class="mt-1 text-sm text-zinc-500">
                Αριθμός Δελτίου: <span class="font-medium">{{ $ticket->reference_number ?? '—' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="back"
                class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-black-600 hover:bg-black-50 dark:border-black-800 dark:text-red-400 dark:hover:bg-red-950/20"
            >
                Πίσω
            </button>

            <button
                type="button"
                wire:click="confirmDelete"
                class="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950/20"
            >
                Διαγραφή
            </button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold">Στοιχεία Αναγγελίας</h2>

            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Τμήμα / Διεύθυνση / Υπηρεσία</dt>
                    <dd>{{ $ticket->department ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Σταθμός / Κλιμάκιο</dt>
                    <dd>{{ $ticket->station_unit ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Πρόσωπο Επικοινωνίας</dt>
                    <dd>{{ $ticket->contact_person ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Τηλέφωνο Επικοινωνίας</dt>
                    <dd>{{ $ticket->contact_phone ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Χώρος Εγκατάστασης</dt>
                    <dd>{{ $ticket->installation_location ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Αριθμός Οχήματος</dt>
                    <dd>{{ $ticket->vehicle_registration ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Τρόπος Αναγγελίας</dt>
                    <dd>
                        @switch($ticket->report_channel)
                            @case('email') Ηλεκτρονικό Ταχυδρομείο @break
                            @case('fax') Τηλεομοιοτυπία (fax) @break
                            @case('phone') Τηλέφωνο @break
                            @default —
                        @endswitch
                    </dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Καταχωρίστηκε από</dt>
                    <dd class="whitespace-pre-line">{{ $ticket->recorded_by ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Ημερομηνία Καταχώρισης</dt>
                    <dd>{{ $ticket->created_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold">Στοιχεία Δελτίου</h2>

            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Θέμα</dt>
                    <dd>{{ $ticket->title ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Κατάσταση</dt>
                    <dd><x-status-badge :status="$ticket->status" /></dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Ώρα Αναγγελίας</dt>
                    <dd>{{ $ticket->opened_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <dt class="text-zinc-500">Ημερομηνία Ολοκλήρωσης</dt>
                    <dd>{{ $ticket->closed_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                </div>

                <div>
                    <dt class="mb-2 text-zinc-500">Στοιχεία Εξοπλισμού</dt>
                    <dd>
                        @if(!empty($ticket->equipment_types))
                            <ul class="list-disc space-y-1 ps-5">
                                @foreach($ticket->equipment_types as $equipment)
                                    <li>{{ $equipment }}</li>
                                @endforeach
                            </ul>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="mb-4 font-semibold">Περιγραφή Βλάβης</h2>
        <p class="whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">
            {{ $ticket->fault_description ?: $ticket->description ?: '—' }}
        </p>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="mb-4 font-semibold">Ιστορικό Ενεργειών</h2>

        <div class="space-y-4">
            @forelse($ticket->updates as $update)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium">{{ $update->admin->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $update->action_at?->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($update->old_status && $update->new_status)
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                            Αλλαγή κατάστασης από
                            <strong>{{ str_replace('_', ' ', $update->old_status) }}</strong>
                            σε
                            <strong>{{ str_replace('_', ' ', $update->new_status) }}</strong>
                        </p>
                    @endif

                    @if($update->note)
                        <p class="mt-2 whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-300">{{ $update->note }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-zinc-500">Δεν υπάρχουν ακόμη ενέργειες.</p>
            @endforelse
        </div>
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold">Διαγραφή Δελτίου</h2>
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                    Αυτή η ενέργεια θα διαγράψει οριστικά το δελτίο από την ενεργή προβολή.
                    Το δελτίο θα παραμείνει στη βάση δεδομένων ως soft deleted.
                    Θέλετε σίγουρα να συνεχίσετε;
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
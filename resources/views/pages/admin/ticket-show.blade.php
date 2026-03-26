<?php

use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Notifications\TicketNoteAddedNotification;
use App\Notifications\TicketStatusChangedNotification;
use Livewire\Component;

new class extends Component {
    public Ticket $ticket;
    public string $status = '';
    public string $note = '';
    public string $resolution_action = '';
    public string $resolved_by = '';
    public string $internal_notes = '';


    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['customer', 'updates.admin']);
        $this->status = $ticket->status;
        $this->resolution_action = $ticket->resolution_action ?? '';
        $this->resolved_by = $ticket->resolved_by ?? '';
        $this->internal_notes = $ticket->internal_notes ?? '';
    }

    public function updateTicket(): void
    {
        $this->validate([
            'status' => ['required', 'in:backlog,in_progress,done'],
            'note' => ['nullable', 'string', 'max:2000'],
            'resolution_action' => ['nullable', 'string', 'max:5000'],
            'resolved_by' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = $this->ticket->status;
        $newStatus = $this->status;

        $statusChanged = $oldStatus !== $newStatus;
        $hasNote = trim($this->note) !== '';

        if (! $statusChanged && ! $hasNote && trim($this->resolution_action) === trim((string) $this->ticket->resolution_action) && trim($this->resolved_by) === trim((string) $this->ticket->resolved_by) && trim($this->internal_notes) === trim((string) $this->ticket->internal_notes)) {
            session()->flash('error', 'Δεν έγινε καμία αλλαγή.');
            return;
        }

        $this->ticket->status = $newStatus;
        $this->ticket->resolution_action = $this->resolution_action ?: null;
        $this->ticket->resolved_by = $this->resolved_by ?: null;
        $this->ticket->internal_notes = $this->internal_notes ?: null;

        if ($statusChanged && $newStatus === 'done' && ! $this->ticket->closed_at) {
            $this->ticket->closed_at = now();
            $this->ticket->resolved_at = now();
        }

        if ($statusChanged && $newStatus !== 'done') {
            $this->ticket->closed_at = null;
            $this->ticket->resolved_at = null;
        }

        $this->ticket->save();

        TicketUpdate::create([
            'ticket_id' => $this->ticket->id,
            'admin_id' => auth()->id(),
            'old_status' => $statusChanged ? $oldStatus : null,
            'new_status' => $statusChanged ? $newStatus : null,
            'note' => $hasNote ? $this->note : null,
            'action_at' => now(),
        ]);

        $this->ticket->refresh()->load(['customer', 'updates.admin']);

        if ($statusChanged && $newStatus === 'done') {
            $this->ticket->customer->notify(
                new TicketStatusChangedNotification(
                    $this->ticket,
                    $oldStatus,
                    $newStatus,
                    $hasNote ? $this->note : null
                )
            );
        }

        $this->note = '';

        session()->flash('success', 'Το δελτίο ενημερώθηκε επιτυχώς.');
    }
};

?>

<div class="mx-auto max-w-7xl space-y-6">
    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Διαχείριση Δελτίου Βλάβης</h1>
            <p class="mt-1 text-sm text-zinc-500">
                Αριθμός Δελτίου: <span class="font-medium">{{ $ticket->reference_number ?? '—' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-status-badge :status="$ticket->status" />
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="mb-4 font-semibold">Στοιχεία Αναγγελίας</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-zinc-500">Τμήμα / Υπηρεσία</dt>
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
                            <dt class="text-zinc-500">Τηλέφωνο</dt>
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

                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-zinc-500">Πελάτης / Χρήστης</dt>
                            <dd>{{ $ticket->customer->name }} / {{ $ticket->customer->email }}</dd>
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
        </div>

        <div class="space-y-6">
            <form wire:submit="updateTicket" class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold">Διαχείριση Δελτίου</h2>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Κατάσταση</label>
                        <select wire:model="status" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                            <option value="backlog">Σε Εκκρεμότητα</option>
                            <option value="in_progress">Σε Εξέλιξη</option>
                            <option value="done">Ολοκληρωμένο</option>
                        </select>
                        @error('status') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Ενέργεια / Σχόλιο Διαχειριστή</label>
                        <textarea wire:model="note" rows="5" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                        @error('note') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Ενέργεια Αποκατάστασης</label>
                        <textarea wire:model="resolution_action" rows="5" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                        @error('resolution_action') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Αποκαταστάθηκε από</label>
                        <input wire:model="resolved_by" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        @error('resolved_by') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Για Εσωτερική Χρήση</label>
                        <textarea wire:model="internal_notes" rows="5" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                        @error('internal_notes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">
                        Αποθήκευση Ενημέρωσης
                    </button>
                </div>
            </form>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-4 font-semibold">Στοιχεία Αποκατάστασης</h2>

                <dl class="space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-zinc-500">Ημ/νία Αποκατάστασης</dt>
                        <dd>{{ $ticket->resolved_at?->format('d/m/Y H:i') ?: '—' }}</dd>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <dt class="text-zinc-500">Αποκαταστάθηκε από</dt>
                        <dd>{{ $ticket->resolved_by ?: '—' }}</dd>
                    </div>

                    <div>
                        <dt class="mb-2 text-zinc-500">Ενέργεια Αποκατάστασης</dt>
                        <dd class="whitespace-pre-line">{{ $ticket->resolution_action ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
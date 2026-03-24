<?php

use App\Models\Ticket;
use Livewire\Component;

new class extends Component {
    public string $searchReference = '';

    public function updatedSearchReference(): void
    {
        // μόνο για re-render
    }

    public function with(): array
    {
        $user = auth()->user();

        $recentTicketsQuery = $user->tickets()->latest();

        if (trim($this->searchReference) !== '') {
            $recentTicketsQuery->where('reference_number', 'like', '%'.$this->searchReference.'%');
        }

        return [
            'totalTickets' => $user->tickets()->count(),
            'backlogCount' => $user->tickets()->where('status', 'backlog')->count(),
            'inProgressCount' => $user->tickets()->where('status', 'in_progress')->count(),
            'doneCount' => $user->tickets()->where('status', 'done')->count(),
            'recentTickets' => $recentTicketsQuery->take(10)->get(),
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

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Πίνακας Χρήστη</h1>
            <p class="text-sm text-zinc-500">Διαχείριση δελτίων βλάβης.</p>
        </div>

        <a href="{{ route('customer.tickets.create') }}" wire:navigate
           class="inline-flex items-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90 dark:bg-white dark:text-zinc-900">
            Νέο Δελτίο
        </a>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Σύνολο</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalTickets }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Σε Εκκρεμότητα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $backlogCount }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Σε Εξέλιξη</p>
            <p class="mt-2 text-3xl font-semibold">{{ $inProgressCount }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Ολοκληρωμένα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $doneCount }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h2 class="text-lg font-semibold whitespace-nowrap">Πρόσφατα Δελτία</h2>

                <div class="w-52 md:w-52 lg:w-56 shrink-0">
                    <input
                        wire:model.live.debounce.300ms="searchReference"
                        type="text"
                        placeholder="Αριθμός δελτίου..."
                        class="w-52 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                    >
                </div>
            </div>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @forelse($recentTickets as $ticket)
                <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="truncate font-medium">{{ $ticket->title }}</p>

                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types))
                                <span class="text-sm text-zinc-500" style="padding-left: 10px">
                                    {{ implode(' , ', $ticket->equipment_types) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1 text-sm text-zinc-500">
                            <span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Αρ. Δελτίου:</span>
                                {{ $ticket->reference_number ?? '—' }}
                            </span>

                            <span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Δημιουργήθηκε:</span>
                                {{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-status-badge :status="$ticket->status" />

                        <a
                            href="{{ route('customer.tickets.show', $ticket) }}"
                            wire:navigate
                            class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            Προβολή
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-zinc-500">
                    Δεν βρέθηκαν δελτία.
                </div>
            @endforelse
        </div>
    </div>
</div>
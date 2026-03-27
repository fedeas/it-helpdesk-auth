<?php

use App\Models\Ticket;
use Livewire\Component;

new class extends Component {
    public string $searchReference = '';
    public string $selectedStatusFilter = '';

    public function toggleStatusFilter(string $status): void
    {
        $this->selectedStatusFilter = $this->selectedStatusFilter === $status ? '' : $status;
    }

    public function clearStatusFilter(): void
    {
        $this->selectedStatusFilter = '';
    }

    public function with(): array
    {
        $user = auth()->user();

        $recentTicketsQuery = $user->tickets()->latest();

        if (trim($this->searchReference) !== '') {
            $recentTicketsQuery->where('reference_number', 'like', '%'.$this->searchReference.'%');
        }

        if ($this->selectedStatusFilter !== '') {
            $recentTicketsQuery->where('status', $this->selectedStatusFilter);
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
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 3500)"
            class="fixed right-4 top-4 z-[9999] w-full max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-xl dark:border-emerald-900 dark:bg-emerald-900/90 dark:text-emerald-300"
        >
            <div class="flex items-start justify-between gap-3">
                <span>{{ session('success') }}</span>

                <button
                    type="button"
                    @click="show = false"
                    class="text-emerald-700 hover:opacity-70 dark:text-emerald-300"
                >
                    ✕
                </button>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 4500)"
                class="fixed right-4 top-4 z-[9999] w-full max-w-md rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-xl dark:border-red-900 dark:bg-red-900/90 dark:text-red-300"
            >
                <div class="flex items-start justify-between gap-3">
                    <span>{{ session('error') }}</span>

                    <button
                        type="button"
                        @click="show = false"
                        class="text-red-700 hover:opacity-70 dark:text-red-300"
                    >
                        ✕
                    </button>
                </div>
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
        <button
            type="button"
            wire:click="clearStatusFilter"
            class="rounded-2xl border p-5 text-left shadow-sm transition
                {{ $selectedStatusFilter === '' ? 'border-zinc-400 bg-zinc-100 ring-2 ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-800' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σύνολο</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalTickets }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('backlog')"
            class="rounded-2xl border p-5 text-left shadow-sm transition
                {{ $selectedStatusFilter === 'backlog' ? 'border-zinc-400 bg-zinc-100 ring-2 ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-800' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σε Εκκρεμότητα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $backlogCount }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('in_progress')"
            class="rounded-2xl border p-5 text-left shadow-sm transition
                {{ $selectedStatusFilter === 'in_progress' ? 'border-amber-300 bg-amber-50 ring-2 ring-amber-200 dark:border-amber-700 dark:bg-amber-900/20' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σε Εξέλιξη</p>
            <p class="mt-2 text-3xl font-semibold">{{ $inProgressCount }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('done')"
            class="rounded-2xl border p-5 text-left shadow-sm transition
                {{ $selectedStatusFilter === 'done' ? 'border-emerald-300 bg-emerald-50 ring-2 ring-emerald-200 dark:border-emerald-700 dark:bg-emerald-900/20' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Ολοκληρωμένα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $doneCount }}</p>
        </button>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h2 class="text-lg font-semibold whitespace-nowrap">Πρόσφατα Δελτία</h2>

                <div class="w-full md:w-52 lg:w-56 shrink-0">
                    <input
                        wire:model.live.debounce.300ms="searchReference"
                        type="text"
                        placeholder="Αριθμός δελτίου..."
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                    >
                </div>
            </div>
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @forelse($recentTickets as $ticket)
                <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="font-medium">{{ $ticket->title }}</p>

                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types))
                                <span class="text-xs text-zinc-500" style="padding-left:10px;padding-top:2px;">
                                     {{ implode(' / ', $ticket->equipment_types) }}
                                </span>
                            @endif
                        </div>

                        <div class="text-sm text-zinc-500">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Αρ. Δελτίου:</span>
                                {{ $ticket->reference_number ?? '—' }}
                            </div>

                            <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>

                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Δημιουργήθηκε:</span>
                                {{ $ticket->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </div>
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
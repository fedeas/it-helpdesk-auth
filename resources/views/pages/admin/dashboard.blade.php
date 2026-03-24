<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $openSort = 'created_desc';
    public string $closedSort = 'closed_desc';

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'openPage');
        $this->resetPage(pageName: 'closedPage');
    }

    public function updatingOpenSort(): void
    {
        $this->resetPage(pageName: 'openPage');
    }

    public function updatingClosedSort(): void
    {
        $this->resetPage(pageName: 'closedPage');
    }

    protected function applySearch($query)
    {
        return $query->when($this->search !== '', function ($q) {
            $q->where(function ($sub) {
                $sub->where('title', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        });
    }

    protected function sortOpen($query)
    {
        return match ($this->openSort) {
            'created_asc' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    protected function sortClosed($query)
    {
        return match ($this->closedSort) {
            'closed_asc' => $query->orderBy('closed_at', 'asc'),
            'created_asc' => $query->orderBy('created_at', 'asc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('closed_at', 'desc'),
        };
    }

    public function with(): array
    {
        $openBase = Ticket::with('customer')
            ->whereIn('status', ['backlog', 'in_progress']);

        $closedBase = Ticket::with('customer')
            ->where('status', 'done');

        $openBase = $this->applySearch($openBase);
        $closedBase = $this->applySearch($closedBase);

        return [
            'totalTickets' => Ticket::count(),
            'backlogCount' => Ticket::where('status', 'backlog')->count(),
            'inProgressCount' => Ticket::where('status', 'in_progress')->count(),
            'doneCount' => Ticket::where('status', 'done')->count(),
            'openTickets' => $this->sortOpen($openBase)->paginate(10, ['*'], 'openPage'),
            'closedTickets' => $this->sortClosed($closedBase)->paginate(10, ['*'], 'closedPage'),
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

    <div>
        <h1 class="text-2xl font-semibold">Πίνακας Διαχείρισης</h1>
        <p class="text-sm text-zinc-500">Παρακολούθηση ενεργών και ολοκληρωμένων δελτίων.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Σύνολο Δελτίων</p>
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

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Αναζήτηση με θέμα, όνομα πελάτη ή email..."
            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
        >
    </div>

    <section class="space-y-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold">Ανοιχτά Δελτία</h2>
                <p class="text-sm text-zinc-500">Δελτία σε εκκρεμότητα και σε εξέλιξη.</p>
            </div>

            <div class="w-full md:w-64">
                <select
                    wire:model.live="openSort"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                >
                    <option value="created_desc">Νεότερα πρώτα</option>
                    <option value="created_asc">Παλαιότερα πρώτα</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θεμα</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Πελατης</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Κατασταση</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Ημ/νια Δημιουργιας</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενεργειες</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($openTickets as $ticket)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-medium">{{ $ticket->title }}</p>

                                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types))
                                                <span class="text-sm text-zinc-500" style="padding-left: 10px">
                                                    {{ implode(' , ', $ticket->equipment_types) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    {{ $ticket->customer->name }}<br>
                                    <span class="text-zinc-500">{{ $ticket->customer->email }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <x-status-badge :status="$ticket->status" />
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-500">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('admin.tickets.show', $ticket) }}"
                                            wire:navigate
                                            class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        >
                                            Διαχείριση
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">
                                    Δεν βρέθηκαν ανοιχτά δελτία.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $openTickets->links() }}
    </section>

    <section class="space-y-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold">Κλειστά Δελτία</h2>
                <p class="text-sm text-zinc-500">Τα ολοκληρωμένα δελτία εμφανίζονται εδώ.</p>
            </div>

            <div class="w-full md:w-64">
                <select
                    wire:model.live="closedSort"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                >
                    <option value="closed_desc">Νεότερα κλεισίματα πρώτα</option>
                    <option value="closed_asc">Παλαιότερα κλεισίματα πρώτα</option>
                    <option value="created_desc">Νεότερες δημιουργίες πρώτα</option>
                    <option value="created_asc">Παλαιότερες δημιουργίες πρώτα</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θεμα</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Πελατης</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Κατασταση</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Δημιουργια</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Ολοκληρωση</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενεργειες</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($closedTickets as $ticket)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-medium">{{ $ticket->title }}</p>

                                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types))
                                                <span class="text-sm text-zinc-500" style="padding-left: 10px">
                                                    {{ implode(' , ', $ticket->equipment_types) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    {{ $ticket->customer->name }}<br>
                                    <span class="text-zinc-500">{{ $ticket->customer->email }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <x-status-badge :status="$ticket->status" />
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-500">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-zinc-500">
                                    {{ $ticket->closed_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('admin.tickets.show', $ticket) }}"
                                            wire:navigate
                                            class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        >
                                            Προβολή
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500">
                                    Δεν βρέθηκαν ολοκληρωμένα δελτία.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $closedTickets->links() }}
    </section>
</div>
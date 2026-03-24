<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $status = '';
    public string $search = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'tickets' => Ticket::with('customer')
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->when(
                    $this->search !== '',
                    fn ($q) => $q->where(function ($sub) {
                        $sub->where('title', 'like', '%'.$this->search.'%')
                            ->orWhereHas('customer', fn ($cq) => $cq
                                ->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%'));
                    })
                )
                ->latest()
                ->paginate(12),
        ];
    }
};

?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">All Tickets</h1>
        <p class="text-sm text-zinc-500">Review, filter, and manage customer support requests.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="md:col-span-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by title, customer name, or email..."
                   class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900">
        </div>

        <div>
            <select wire:model.live="status"
                    class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900">
                <option value="">All Statuses</option>
                <option value="backlog">Backlog</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Created</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-4 font-medium">{{ $ticket->title }}</td>
                        <td class="px-4 py-4 text-sm">{{ $ticket->customer->name }}<br><span class="text-zinc-500">{{ $ticket->customer->email }}</span></td>
                        <td class="px-4 py-4"><x-status-badge :status="$ticket->status" /></td>
                        <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" wire:navigate class="text-sm font-medium text-zinc-900 underline dark:text-white">
                                Manage
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">No tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $tickets->links() }}
    </div>
</div>
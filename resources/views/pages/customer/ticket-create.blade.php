<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

new class extends Component {
    public string $department = '';
    public string $station_unit = '';
    public string $contact_person = '';
    public string $contact_phone = '';
    public string $installation_location = '';
    public string $vehicle_registration = '';
    public string $report_channel = 'email';

    public array $equipment_types = [];

    public string $title = '';
    public string $description = '';
    public string $fault_description = '';
    public string $recorded_by = '';

    public function save()
    {
        $this->validate([
            'department' => ['required', 'string', 'max:255'],
            'station_unit' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'installation_location' => ['nullable', 'string', 'max:255'],
            'vehicle_registration' => ['nullable', 'string', 'max:255'],
            'report_channel' => ['required', 'in:email,fax,phone'],
            'equipment_types' => ['nullable', 'array'],
            'title' => ['required', 'string', 'max:255'],
            'fault_description' => ['required', 'string', 'min:5'],
            'recorded_by' => ['required', 'string', 'max:1000'],
        ]);

        $ticket = Ticket::create([
            'reference_number' => Ticket::generateReferenceNumber(),
            'customer_id' => auth()->id(),
            'department' => $this->department,
            'station_unit' => $this->station_unit,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'installation_location' => $this->installation_location,
            'vehicle_registration' => $this->vehicle_registration,
            'report_channel' => $this->report_channel,
            'equipment_types' => $this->equipment_types,
            'title' => $this->title,
            'description' => $this->fault_description,
            'fault_description' => $this->fault_description,
            'status' => 'backlog',
            'opened_at' => now(),
            'recorded_by' => $this->recorded_by,
        ]);

        $ticket->load('customer');

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewTicketCreatedNotification($ticket));

        session()->flash('success', 'Το δελτίο βλάβης καταχωρήθηκε επιτυχώς.');

        return $this->redirect(route('customer.dashboard'), navigate: true);
    }
};

?>

<div class="mx-auto max-w-5xl space-y-6">
    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold">Καταχώρηση Δελτίου Βλάβης</h1>
        <p class="text-sm text-zinc-500">Συμπληρώστε τα στοιχεία του δελτίου σύμφωνα με τη βλάβη που αναφέρεται.</p>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα</label>
                <input wire:model="department" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('department') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Σταθμός / Κλιμάκιο</label>
                <input wire:model="station_unit" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('station_unit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Πρόσωπο Επικοινωνίας</label>
                <input wire:model="contact_person" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('contact_person') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Τηλέφωνο Επικοινωνίας</label>
                <input wire:model="contact_phone" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('contact_phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Χώρος Εγκατάστασης</label>
                <input wire:model="installation_location" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('installation_location') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Αριθμός Εγγραφής Οχήματος</label>
                <input wire:model="vehicle_registration" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('vehicle_registration') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Τρόπος Αναγγελίας</label>
            <select wire:model="report_channel" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="email">Ηλεκτρονικό Ταχυδρομείο (e-mail)</option>
                <option value="fax">Τηλεομοιοτυπία (fax)</option>
                <option value="phone">Τηλέφωνο</option>
            </select>
            @error('report_channel') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-3 block text-sm font-medium">Στοιχεία Εξοπλισμού</label>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="GPS/GPRS Module στο Αυτοκίνητο">
                    <span>GPS/GPRS Module στο Αυτοκίνητο</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Τερματικός Ηλεκτρονικός Υπολογιστής">
                    <span>Τερματικός Ηλεκτρονικός Υπολογιστής</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Android tablet στο Αυτοκίνητο">
                    <span>Android tablet στο Αυτοκίνητο</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Server (Λογισμικό)">
                    <span>Server (Λογισμικό)</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Φορητό Σύστημα Εντοπισμού">
                    <span>Φορητό Σύστημα Εντοπισμού</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Server (Hardware)">
                    <span>Server (Hardware)</span>
                </label>
            </div>
            @error('equipment_types') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Θέμα / Τίτλος Βλάβης</label>
            <input wire:model="title" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Περιγραφή Βλάβης</label>
            <textarea wire:model="fault_description" rows="7" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
            @error('fault_description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Καταχωρήθηκε από</label>
                <input wire:model="recorded_by" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('recorded_by') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Ημερομηνία Καταχώρισης</label>
                <input
                    type="text"
                    value="{{ now()->format('d/m/Y H:i') }}"
                    disabled
                    class="w-full rounded-xl border border-zinc-300 bg-zinc-100 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                >
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('customer.tickets.index') }}" wire:navigate class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium dark:border-zinc-700">
                Ακύρωση
            </a>

            <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">
                Καταχώρηση Δελτίου
            </button>
        </div>
    </form>
</div>
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

    protected function messages(): array
    {
        return [
            'department.required' => 'Το πεδίο Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα είναι υποχρεωτικό.',
            'contact_person.required' => 'Το Πρόσωπο Επικοινωνίας είναι υποχρεωτικό.',
            'contact_phone.required' => 'Το τηλέφωνο επικοινωνίας είναι υποχρεωτικό.',
            'contact_phone.regex' => 'Το τηλέφωνο πρέπει να περιέχει ακριβώς 8 ψηφία.',
            'report_channel.required' => 'Ο τρόπος αναγγελίας είναι υποχρεωτικός.',
            'report_channel.in' => 'Ο τρόπος αναγγελίας δεν είναι έγκυρος.',
            'equipment_types.required' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο εξοπλισμού.',
            'equipment_types.min' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο εξοπλισμού.',
            'title.required' => 'Το θέμα / τίτλος βλάβης είναι υποχρεωτικό.',
            'title.max' => 'Το θέμα / τίτλος βλάβης είναι πολύ μεγάλο.',
            'fault_description.required' => 'Η περιγραφή βλάβης είναι υποχρεωτική.',
            'fault_description.min' => 'Η περιγραφή βλάβης πρέπει να έχει τουλάχιστον 5 χαρακτήρες.',
            'recorded_by.required' => 'Το πεδίο Καταχωρίστηκε από είναι υποχρεωτικό.',
            'recorded_by.max' => 'Το πεδίο Καταχωρίστηκε από είναι πολύ μεγάλο.',
        ];
    }

    public function save()
    {
        $this->validate([
            'department' => ['required', 'string', 'max:255'],
            'station_unit' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'installation_location' => ['nullable', 'string', 'max:255'],
            'vehicle_registration' => ['nullable', 'string', 'max:255'],
            'report_channel' => ['required', 'in:email,fax,phone'],
            'equipment_types' => ['required', 'array', 'min:1'],
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

        return $this->redirect(route('customer.tickets.index'), navigate: true);
    }
};

?>

<div class="mx-auto max-w-5xl space-y-6">
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

    <div>
        <h1 class="text-2xl font-semibold">Καταχώρηση Δελτίου Βλάβης</h1>
        <p class="text-sm text-zinc-500">Συμπληρώστε τα στοιχεία του δελτίου σύμφωνα με τη βλάβη που αναφέρεται.</p>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα</label>
                <input
                    wire:model="department"
                    type="text"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('department') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Σταθμός / Κλιμάκιο</label>
                <input
                    wire:model="station_unit"
                    type="text"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('station_unit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Πρόσωπο Επικοινωνίας</label>
                <input
                    wire:model="contact_person"
                    type="text"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('contact_person') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Τηλέφωνο Επικοινωνίας</label>

                <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-950">
                    <div class="flex items-center border-e border-zinc-300 bg-zinc-100 px-4 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        +357
                    </div>

                    <input
                        wire:model.live="contact_phone"
                        type="text"
                        inputmode="numeric"
                        maxlength="8"
                        placeholder="99999999"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,8)"
                        class="w-full border-0 bg-transparent px-4 py-3 text-sm focus:outline-none"
                    >
                </div>

                <p class="mt-1 text-xs text-zinc-500">Συμπληρώστε 8 ψηφία, π.χ. 99123456</p>

                @error('contact_phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Χώρος Εγκατάστασης</label>
                <input
                    wire:model="installation_location"
                    type="text"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('installation_location') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Αριθμός Εγγραφής Οχήματος</label>
                <input
                    wire:model="vehicle_registration"
                    type="text"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                >
                @error('vehicle_registration') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Τρόπος Αναγγελίας</label>
            <select
                wire:model="report_channel"
                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
            >
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
            <input
                wire:model="title"
                type="text"
                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
            >
            @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Περιγραφή Βλάβης</label>
            <textarea
                wire:model="fault_description"
                rows="7"
                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
            ></textarea>
            @error('fault_description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Καταχωρίστηκε από</label>
                <textarea
                    wire:model="recorded_by"
                    rows="4"
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                    placeholder="Συμπληρώστε το άτομο που έκανε την καταχώριση"
                ></textarea>
                @error('recorded_by') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Αριθμός Δελτίου / Αναφοράς</label>
                <input
                    type="text"
                    value="Θα δημιουργηθεί αυτόματα με την καταχώρηση"
                    disabled
                    class="w-full rounded-xl border border-zinc-300 bg-zinc-100 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                >
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a
                href="{{ route('customer.tickets.index') }}"
                wire:navigate
                class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium transition duration-150 hover:scale-[1.02] hover:bg-zinc-50 hover:shadow-sm active:scale-[0.98] dark:border-zinc-700 dark:hover:bg-zinc-800"
            >
                Ακύρωση
            </a>

            <button
                type="submit"
                class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-150 hover:scale-[1.02] hover:shadow-md active:scale-[0.98] active:shadow-sm dark:bg-white dark:text-zinc-900"
            >
                Καταχώρηση Δελτίου
            </button>
        </div>
    </form>
</div>
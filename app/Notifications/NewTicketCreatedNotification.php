<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isAdmin = ($notifiable->role ?? null) === 'admin';

        if ($isAdmin) {
            return (new MailMessage)
                ->subject('Άνοιξε νέο Δελτίο Βλάβης')
                ->greeting('Γεια σας '.$notifiable->name.',')
                ->line('Έχει ανοιχτεί νέο δελτίο βλάβης στο σύστημα.')
                ->line('Αριθμός Δελτίου: '.($this->ticket->reference_number ?? '—'))
                ->line('Θέμα: '.$this->ticket->title)
                ->line('Πρόσωπο Επικοινωνίας: '.($this->ticket->contact_person ?? '—'))
                ->line('Κατάσταση: '.$this->statusLabel($this->ticket->status))
                ->action('Προβολή Δελτίου', route('admin.tickets.show', $this->ticket))
                ->salutation('Με εκτίμηση, Computerland IT Services');
        }

        return (new MailMessage)
            ->subject('Παραλαβή Δελτίου Βλάβης')
            ->greeting('Γεια σας '.$notifiable->name.',')
            ->line('Έχουμε παραλάβει το δελτίο βλάβης σας και θα το εξετάσουμε το συντομότερο δυνατό.')
            ->line('Αριθμός Δελτίου: '.($this->ticket->reference_number ?? '—'))
            ->line('Θέμα: '.$this->ticket->title)
            ->line('Κατάσταση: '.$this->statusLabel($this->ticket->status))
            ->action('Προβολή Δελτίου', route('customer.tickets.show', $this->ticket))
            ->salutation('Με εκτίμηση, Computerland IT Services');
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Σε Εξέλιξη',
            'done' => 'Ολοκληρωμένο',
            default => 'Σε Εκκρεμότητα',
        };
    }
}
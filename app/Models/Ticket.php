<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'customer_id',
        'department',
        'station_unit',
        'contact_person',
        'contact_phone',
        'installation_location',
        'vehicle_registration',
        'report_channel',
        'equipment_types',
        'title',
        'description',
        'fault_description',
        'status',
        'opened_at',
        'closed_at',
        'recorded_by',
        'internal_notes',
        'resolved_at',
        'resolution_action',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'deleted_at' => 'datetime',
            'equipment_types' => 'array',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function updates()
    {
        return $this->hasMany(TicketUpdate::class)->latest('action_at');
    }

    public static function generateReferenceNumber(): string
    {
        $lastTicket = self::withTrashed()
            ->whereNotNull('reference_number')
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastTicket && preg_match('/(\d+)$/', $lastTicket->reference_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return 'AVL-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiEventModel extends Model
{
    protected $table = 'tamo_ai_events';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'status',
        'event_date',
        'weekday',
        'client',
        'address',
        'neighborhood',
        'start_time',
        'set_count',
        'interval_minutes',
        'total_fee',
        'payment_done',
        'payment_status',
        'split_condition',
        'expected_members',
        'day_color',
        'sketch_model',
        'sound_contract',
        'viola_player',
        'drummer',
        'notes',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps = true;
}

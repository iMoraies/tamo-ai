<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiCashBoxMovementModel extends Model
{
    protected $table = 'tamo_ai_cash_box_movements';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id',
        'receipt_id',
        'type',
        'category',
        'description',
        'amount',
        'movement_date',
        'payment_method',
        'notes',
        'responsible_user_id',
        'created_by',
        'deleted_at',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

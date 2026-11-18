<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiPaymentModel extends Model
{
    protected $table = 'tamo_ai_payments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id',
        'person_type',
        'member_id',
        'freelancer_id',
        'name',
        'role',
        'expected_amount',
        'paid_amount',
        'status',
        'payment_method',
        'pix_key',
        'paid_at',
        'receipt_id',
        'notes',
    ];
    protected $useTimestamps = true;
}

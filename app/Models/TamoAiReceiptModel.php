<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiReceiptModel extends Model
{
    protected $table = 'tamo_ai_receipts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id',
        'payment_id',
        'cash_entry_id',
        'title',
        'type',
        'category',
        'amount',
        'payment_date',
        'payment_method',
        'paid_by',
        'received_by',
        'member_id',
        'freelancer_id',
        'file_original_name',
        'file_name',
        'file_path',
        'file_mime',
        'file_size',
        'notes',
        'uploaded_by',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiEventFinancialModel extends Model
{
    protected $table = 'tamo_ai_event_financials';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id',
        'contracted_amount',
        'received_amount',
        'received_status',
        'received_date',
        'received_method',
        'total_expenses',
        'total_freelancers',
        'group_cash_amount',
        'amount_to_split',
        'split_members_count',
        'amount_per_member',
        'manual_adjustment',
        'notes',
        'closed_at',
        'closed_by',
    ];
    protected $useTimestamps = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiEmailLogModel extends Model
{
    protected $table = 'tamo_ai_email_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'event_id',
        'report_month',
        'report_year',
        'subject',
        'recipients_json',
        'message',
        'attachments_json',
        'status',
        'error_message',
        'sent_by',
        'sent_at',
    ];
    protected $useTimestamps = true;
}

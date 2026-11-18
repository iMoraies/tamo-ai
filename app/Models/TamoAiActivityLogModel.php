<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiActivityLogModel extends Model
{
    protected $table = 'tamo_ai_activity_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'action',
        'module',
        'related_id',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
}

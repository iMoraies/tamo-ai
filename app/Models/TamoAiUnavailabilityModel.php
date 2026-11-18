<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiUnavailabilityModel extends Model
{
    protected $table = 'tamo_ai_unavailabilities';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

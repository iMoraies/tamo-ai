<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiAttendanceModel extends Model
{
    protected $table = 'tamo_ai_event_attendance';
    protected $primaryKey = 'id';
    protected $allowedFields = ['event_id', 'user_id', 'status'];
    protected $useTimestamps = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiEventFreelancerModel extends Model
{
    protected $table = 'tamo_ai_event_freelancers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['event_id', 'name', 'instrument', 'fee'];
    protected $useTimestamps = true;
}

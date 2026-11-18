<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiFreelancerModel extends Model
{
    protected $table = 'tamo_ai_freelancers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'role', 'email', 'phone', 'pix_key', 'default_amount', 'status', 'notes'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

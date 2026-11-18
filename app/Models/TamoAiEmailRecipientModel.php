<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiEmailRecipientModel extends Model
{
    protected $table = 'tamo_ai_email_recipients';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'type', 'status', 'notes'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

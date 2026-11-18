<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiMaterialModel extends Model
{
    protected $table = 'tamo_ai_materials';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'category',
        'estimated_price',
        'product_url',
        'status',
        'urgency',
        'priority',
        'needed_until',
        'notes',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
}

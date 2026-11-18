<?php

namespace App\Models;

use CodeIgniter\Model;

class TamoAiUserModel extends Model
{
    protected $table = 'tamo_ai_users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'role',
        'active',
        'phone',
        'main_instrument',
        'pix_key',
        'pix_key_type',
        'last_login_at',
    ];
    protected $useTimestamps = true;
}

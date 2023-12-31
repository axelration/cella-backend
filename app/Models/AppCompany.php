<?php

namespace App\Models;

use CodeIgniter\Model;

class AppCompany extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'app_company';
    protected $primaryKey       = 'acm_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_name',
        'address',
        'mobile_phone',
        'email',
        'cusr_id',
        'ctime',
        'musr_id',
        'mtime',
        'dusr_id',
        'dtime',
        'is_deleted',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'ctime';
    protected $updatedField  = 'mtime';
    protected $deletedField  = 'dtime';
}

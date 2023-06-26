<?php

namespace App\Models;

use CodeIgniter\Model;

class AppRole extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'approles';
    protected $primaryKey       = 'arl_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'cusr_id',
        'ctime',
        'musr_id',
        'mtime',
        'dusr_id',
        'dtime',
        'is_deleted',
    ];

    // Settings
        // Dates
        protected $useTimestamps = false;
        protected $dateFormat    = 'datetime';
        protected $createdField  = 'ctime';
        protected $updatedField  = 'mtime';
        protected $deletedField  = 'dtime';

        // Validation
        protected $validationRules      = [];
        protected $validationMessages   = [];
        protected $skipValidation       = false;
        protected $cleanValidationRules = true;

        // Callbacks
        protected $allowCallbacks = true;
        protected $beforeInsert   = [];
        protected $afterInsert    = [];
        protected $beforeUpdate   = [];
        protected $afterUpdate    = [];
        protected $beforeFind     = [];
        protected $afterFind      = [];
        protected $beforeDelete   = [];
        protected $afterDelete    = [];
    //
}

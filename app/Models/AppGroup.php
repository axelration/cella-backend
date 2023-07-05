<?php

namespace App\Models;

use CodeIgniter\Model;

class AppGroup extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'app_group';
    protected $primaryKey       = 'agp_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'acm_id',
        'address',
        'latitude',
        'longitude',
        'radius',
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

    public function getGroupData($usr_id) {
        $builder = $this->db->table($this->table);
        $builder->select("
            $this->table.agp_id, $this->table.name, $this->table.acm_id,
            $this->table.address, $this->table.latitude, $this->table.longitude,
            $this->table.radius, $this->table.check_in_limit, $this->table.check_out_limit, 
            $this->table.check_in_enable, $this->table.check_out_enable,
            $this->table.check_in_disable, $this->table.check_out_disable
        ");
        $builder->join("app_user","$this->table.acm_id = app_user.acm_id","LEFT");
        $builder->where("app_user.usr_id = '$usr_id'");

        $data = $builder->get()->getRow();
        return $data;
    }
}

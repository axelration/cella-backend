<?php

namespace App\Models;

use CodeIgniter\Model;

class AppAttendance extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'app_attendance';
    protected $primaryKey       = 'att_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'type',
        'check_time',
        'latitude',
        'longitude',
        'device_id',
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

    public function getAttendanceStat($usr_id) {
        $builder = $this->db->table($this->table);
        $builder->select("
            $this->table.cusr_id, 
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 1
            ) total_check_in, 
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 2
            ) total_check_out,
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
            ) total_checked, 
            (
                (SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 1 AND DATE_FORMAT(t.check_time, '%Y%m%d') != DATE_FORMAT(CURDATE(), '%Y%m%d')) - 
                (SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 2 AND DATE_FORMAT(t.check_time, '%Y%m%d') != DATE_FORMAT(CURDATE(), '%Y%m%d'))
            ) total_not_checked,
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 1 AND DATE_FORMAT(t.check_time, '%H%m') > app_group.check_in_limit
            ) late_check_in,
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 2 AND DATE_FORMAT(t.check_time, '%H%m') < app_group.check_out_limit
            ) early_check_out
        ");
        $builder->distinct();
        $builder->join("app_user","$this->table.cusr_id = app_user.usr_id","LEFT");
        $builder->join("app_group","app_user.agp_id = app_group.agp_id","LEFT");
        $builder->where("app_user.usr_id = '$usr_id'");
        $builder->where("$this->table.is_deleted != '1'");

        $data = $builder->get()->getRowArray();
        return $data;
    }
}

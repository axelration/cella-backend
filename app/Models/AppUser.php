<?php

namespace App\Models;

use CodeIgniter\Model;

class AppUser extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'app_user';
    protected $primaryKey       = 'usr_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'password',
        'fullname',
        'mobile_phone',
        'email',
        'device_id',
        'acm_id',
        'agp_id',
        'arl_id',
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

    public function getDetailByUsername($username) {
        $builder = $this->db->table($this->table);
        $builder->select("
            $this->table.usr_id, $this->table.username, $this->table.fullname,
            $this->table.mobile_phone, $this->table.email, $this->table.device_id,
            app_company.company_name, app_group.name AS group_name, 
            app_role.name AS role_name,
            CASE
                WHEN $this->table.is_deleted = '0' THEN 'Aktif'
                WHEN $this->table.is_deleted = '1' THEN 'Tidak Aktif'
                WHEN $this->table.is_deleted = '2' THEN 'Suspend' 
            END AS status
        ");
        $builder->join("app_company","$this->table.acm_id = app_company.acm_id","LEFT");
        $builder->join("app_group","$this->table.agp_id = app_group.agp_id","LEFT");
        $builder->join("app_role","$this->table.arl_id = app_role.arl_id","LEFT");
        $builder->where("$this->table.username = '$username'");

        $data = $builder->get()->getRowArray();
        return $data;
    }
}

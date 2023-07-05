<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;
use stdClass;

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
                WHERE t.type = 1 AND DATE_FORMAT(t.check_time, '%H:%i:%s') > app_group.check_in_limit
            ) late_check_in,
            (
                SELECT COUNT(t.check_time) 
                FROM $this->table t
                WHERE t.type = 2 AND DATE_FORMAT(t.check_time, '%H:%i:%s') < app_group.check_out_limit
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

    public function getAllAttendance($usr_id, $type = '') {
        $data = [];
        $min = $this->select("DATE_FORMAT(MIN(check_time), '%Y-%m-%d') min")->where('is_deleted', '0')->first()['min'];
        $max = $this->select("DATE_FORMAT(MAX(check_time), '%Y-%m-%d') max")->where('is_deleted', '0')->first()['max'];
        $min = new DateTime($min);
        $max = new DateTime($max);

        $no = 1;
        for($i = $min; $i <= $max; $i->modify('+1 day')) {
            $date = $i->format("Y-m-d");
            $displaydate = $i->format("d F");
            $this->db->simpleQuery("SET lc_time_names = 'id_ID'");
            $res = $this->select("att_id, check_time, DATE_FORMAT(check_time, '%d %M') date, DATE_FORMAT(check_time, '%H:%i') time, $this->table.type,
            (CASE 
                WHEN $this->table.type = '1' AND DATE_FORMAT(check_time, '%H:%i:%s') > app_group.check_in_limit THEN 'Terlambat'
                WHEN $this->table.type = '2' AND DATE_FORMAT(check_time, '%H:%i:%s') < app_group.check_out_limit THEN 'Pulang Cepat'
                WHEN check_time IS NULL THEN 'Tidak Ada'
               ELSE 'On Time' END
            ) status
            ")
            ->join('app_user', "$this->table.cusr_id = app_user.usr_id", 'LEFT')
            ->join('app_group', "app_group.agp_id = app_user.agp_id", 'LEFT')
            ->where("DATE_FORMAT(check_time, '%Y-%m-%d') = '$date'")
            ->findAll();

            $time_in = 'null';
            $time_out = 'null';
            $ts = 'On Time';
            foreach($res as $v) {
                $st1 = '';
                $st2 = '';
                if($v['type'] == '1') {
                    $time_in = $v['time'];
                    if($v['status'] != 'On Time') $st1 = $v['status'];
                } else {
                    $time_out = $v['time'];
                    if($v['status'] != 'On Time') $st2 = $v['status'];
                }
                if($st1 != '') {
                    $ts = $st2 != '' ? implode(' - ', [$st1, $st2]) : $st1;
                }
                $displaydate = $v['date'] ?? $i->format("d F");
            }

            $time_status = new stdClass;
            $time_status->type = $ts;
            $time_status->color = 'Green';
            if($ts == 'Terlambat' || $ts == 'Pulang Cepat') {
                $time_status->color = 'Red';
            } else if($ts == 'Tidak Ada') {
                $time_status->color = 'Pink';
            }

            $total_time = new stdClass;
            $total_time->time = '--:--';
            $total_time->color = 'Grey';
            if($time_in != 'null' && $time_out != 'null') {
                $a = new DateTime($time_in);
                $b = new DateTime($time_out);
                $h = $a->diff($b);
                $total_time->time = $h->format('%h jam %i menit');
                $total_time->color = 'Blue';
            }

            $tmp = array(
                'id'    => $no,
                'date'  => $displaydate,
                'in'    => $time_in,
                'out'   => $time_out,
                'status'=> $time_status,
                'total' => $total_time,
            );
            
            $no++;

            array_push($data, $tmp);
        }

        return $data;
    }
}

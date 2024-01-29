<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;
use stdClass;
use TCPDF;

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

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'ctime';
    protected $updatedField  = 'mtime';
    protected $deletedField  = 'dtime';

    public function getAttendanceStat($usr_id) {
        $data = [];
        $tmp = [];
        $min = $this->select("DATE_FORMAT(MIN(check_time), '%Y-%m-%d') min")->where("cusr_id = '$usr_id' AND is_deleted = '0'")->first()['min'];
        $max = $this->select("DATE_FORMAT(MAX(check_time), '%Y-%m-%d') max")->where("cusr_id = '$usr_id' AND is_deleted = '0'")->first()['max'];
        $min = new DateTime($min);
        $max = new DateTime($max);
        $late_ci = 0;
        $early_co = 0;
        $total_c = 0;
        $total_nc = 0;

        for($i = $min; $i <= $max; $i->modify('+1 day')) {
            $date = $i->format("Y-m-d");
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
            ->where("$this->table.cusr_id ='$usr_id' AND DATE_FORMAT(check_time, '%Y-%m-%d') = '$date' AND $this->table.is_deleted = '0'")
            ->findAll();


            // Exclude off days and today
            if(!preg_match('/(saturday|sunday)/i', $i->format('l'))) {
                // Check late time
                $time_in = 'null';
                $time_out = 'null';
                foreach($res as $v) {
                    if($v['type'] == '1') {
                        $time_in = $v['time'];
                        if($v['status'] != 'On Time') $late_ci++;
                    } else {
                        $time_out = $v['time'];
                        if($v['status'] != 'On Time') $early_co++;
                    }
                }

                $restmp = array(
                    'date'  => $date,
                    'in'    => $time_in,
                    'out'   => $time_out,
                );

                array_push($tmp, $restmp);
            }

        }

        $groupModel = new AppGroup();
        $limit = $groupModel->select('check_in_limit, check_out_limit, check_in_enable, check_out_enable, check_in_disable, check_out_disable')
        ->join('app_user', 'app_user.agp_id = app_group.agp_id', 'LEFT')
        ->where("app_user.usr_id = '$usr_id'")
        ->first();

        $now = date('H:i:s');

        // Total Check and not check
        foreach($tmp as $v) {
            if(isset($v['in'])) {
                if($v['in'] != 'null') {
                    $total_c++;
                } else {
                    if ($v['date'] != date('Y-m-d')) {
                        $total_nc++;
                    } else if (strtotime($now) > strtotime($limit['check_in_enable'])) {
                        $total_nc++;
                    }
                }
            }
            if (isset($v['out'])) {
                if ($v['out'] != 'null') {
                    $total_c++;
                } else {
                    if ($v['date'] != date('Y-m-d')) {
                        $total_nc++;
                    } else if (strtotime($now) > strtotime($limit['check_out_enable'])) {
                        $total_nc++;
                    }
                }
            }
        }

        $total_cnc = $total_c + $total_nc;
        $total_le = $late_ci + $early_co;
        $per_c = $total_cnc != 0 ? round($total_c / $total_cnc, 4) * 100 : 0;
        $per_nc = $total_cnc != 0 ? round($total_nc / $total_cnc, 4) * 100 : 0;
        $per_late = $total_le != 0 ? round($late_ci / $total_le, 4) * 100 : 0;
        $per_early = $total_le != 0 ? round($early_co / $total_le, 4) * 100 : 0;

        $data = array(
            'late_check_in' => $late_ci, 
            'early_check_out' => $early_co, 
            'total_checked' => $total_c, 
            'total_not_checked' => $total_nc,
            'percentage_checked' => $per_c . '%',
            'percentage_not_checked' => $per_nc . '%',
            'percentage_late' => $per_late . '%',
            'percentage_early' => $per_early . '%',
        );

        return $data;
    }

    public function getAllAttendance($usr_id, $type = '') {
        $data = [];
        $min = $this->select("DATE_FORMAT(MIN(check_time), '%Y-%m-%d') min")->where('is_deleted', '0')->first()['min'];
        $max = $this->select("DATE_FORMAT(MAX(check_time), '%Y-%m-%d') max")->where('is_deleted', '0')->first()['max'];
        $min = new DateTime($min);
        $max = new DateTime($max);

        $no = 1;
        for($i = $max; $i >= $min; $i->modify('-1 day')) {
            $date = $i->format("Y-m-d");
            $displaydate = $i->format("d F Y");
            // $this->db->simpleQuery("SET lc_time_names = 'id_ID'");
            $res = $this->select("att_id, check_time, DATE_FORMAT(check_time, '%d %M %Y') date, DATE_FORMAT(check_time, '%H:%i') time, $this->table.type,
            (CASE 
                WHEN $this->table.type = '1' AND DATE_FORMAT(check_time, '%H:%i:%s') > app_group.check_in_limit THEN 'Terlambat'
                WHEN $this->table.type = '2' AND DATE_FORMAT(check_time, '%H:%i:%s') < app_group.check_out_limit THEN 'Pulang Cepat'
                WHEN check_time IS NULL THEN 'Tidak Ada'
               ELSE 'On Time' END
            ) status
            ")
            ->join('app_user', "$this->table.cusr_id = app_user.usr_id", 'LEFT')
            ->join('app_group', "app_group.agp_id = app_user.agp_id", 'LEFT')
            ->where("DATE_FORMAT(check_time, '%Y-%m-%d') = '$date' AND $this->table.is_deleted = '0' AND $this->table.cusr_id = '$usr_id'")
            ->findAll();

            $time_in = 'null';
            $time_out = 'null';
            $ts = empty($res) || count($res) < 2 ? 'Tidak Ada' : 'On Time';
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
                $displaydate = $v['date'] ?? $i->format("d F Y");
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
            } else {
                // Label replacement null
                $time_in = $time_in == 'null' ? 'Tidak ada' : $time_in;
                $time_out = $time_out == 'null' ? 'Tidak ada' : $time_out;
            }

            $tmp = array(
                'id'    => strval($no),
                'date'  => $displaydate,
                'in'    => $time_in,
                'out'   => $time_out,
                'total' => $total_time,
                'status'=> $time_status,
            );
            
            $no++;

            array_push($data, $tmp);
        }

        if($type == '' || $type == NULL) {
            return $data;
        } else {
            $user = $this->db->table('app_user')
            ->join('app_group', 'app_user.agp_id = app_group.agp_id', 'LEFT')
            ->select('app_user.*, app_group.name as group_name, app_group.address as group_address')
            ->where('app_user.usr_id', $usr_id)
            ->get()->getFirstRow() ?? NULL;

            return $this->downloadAttendance($data, $user);
        }
    }

    public function downloadAttendance($data, $user = NULL) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Laporan Presensi - ' . date('d-m-Y'));
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set font
        $pdf->SetFont('dejavusans', '', 14, '', true);

        $pdf->AddPage();

        // Set some content to print
        $pdfdata = "";
        $num = 1;
        foreach($data as $k => $v) {
            $pdfdata .= "<tr>";
            $pdfdata .= "<td>" . $num . "</td>";
            foreach($v as $kv => $vv) {
                if(preg_match('/date|in|out/', $kv)) {
                    $pdfdata .= "<td>" . $vv . "</td>";
                } else if(preg_match('/total/', $kv)) {
                    $pdfdata .= "<td>" . $vv->time . "</td>";
                } else if(preg_match('/status/', $kv)) {
                    $pdfdata .= "<td>" . $vv->type . "</td>";
                }
            }
            $pdfdata .= "</tr>";
            $num++;
        }

        setlocale(LC_ALL, 'indonesian');
        $datetgl = strftime("%A, %e %B %Y pada pukul %H:%M:%S");

        $html = "
            <h2>Laporan Presensi</h2>
            <p>Nama &nbsp; : $user->fullname<br>
            Email &nbsp; : $user->email<br>
            Grup &nbsp; : $user->group_name<br>
            Alamat &nbsp; : $user->group_address<br>
            <p>Tanggal data diambil &nbsp; : $datetgl</p>
            <table border='1' cellspacing='3' cellpadding='4'>
            <tr>
                <th><b>No.</b></th>
                <th><b>Tanggal</b></th>
                <th><b>Jam Masuk</b></th>
                <th><b>Jam Keluar</b></th>
                <th><b>Total Jam</b></th>
                <th><b>Keterangan</b></th>
            </tr>
            $pdfdata
            </table>
        ";

        // print_r($html);die();

        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $uname = $user == NULL ? '' : explode(' ', strtolower($user->fullname))[0] . '_';
        $filename = 'laporan_presensi_' . $uname . date('Ymd_His') . '.pdf';
        $pdf->Output(WRITEPATH . 'uploads/' . $filename, 'F');

        $result = new stdClass;
        $result->file_url = base_url('uploads/' . $filename);
        $result->file_name = $filename;

        // print_r($result);die();

        return $result;
    }

}

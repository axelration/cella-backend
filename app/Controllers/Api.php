<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AppCompany;
use App\Models\AppGroup;
use App\Models\AppAttendance;
use App\Models\AppRole;
use App\Models\AppUser;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

class Api extends BaseController
{
    use ResponseTrait;

    protected $userModel;
    protected $companyModel;
    protected $groupModel;
    protected $roleModel;
    protected $attendanceModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = new AppUser();
        $this->companyModel = new AppCompany();
        $this->groupModel = new AppGroup();
        $this->roleModel = new AppRole();
        $this->attendanceModel = new AppAttendance();
    }

    public function index()
    {
        return $this->respond(['status' => 'success', 'message' => 'OK', 'data' => base_url()], 200);
    }

    public function notfound()
    {
        return $this->respond(['status' => 'failed', 'message' => 'Method not specified'], 404);
    }

// Auth
    public function auth() {
        $status = 'failed';
        $msg = 'Username yang anda masukkan tidak ditemukan';

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $device_id = $this->request->getVar('device_id') ?? '';
        
        try {
            $user = $this->userModel->where('username', $username)->first();
            if(is_null($user)) {
                return $this->respond(['status' => $status, 'message' => $msg], 400);
            }
    
            // Validasi password
            $pwd_verify = password_verify($password, $user['password']);
            if(!$pwd_verify) {
                $msg = "Password yang anda masukkan salah";
                return $this->respond(['status' => $status, 'message' => $msg], 400);
            }
    
            // Get User Detail
            $user_detail = $this->userModel->getDetailByUsername(trim($user['username']));
    
            // Device ID validation
            if($user_detail['device_id'] == null && $device_id != '') {
                $this->userModel
                ->where('usr_id', $user_detail['usr_id'])
                ->set(['device_id' => $device_id])
                ->update();

                $user_detail['device_id'] = $device_id;
                
            } else if ($user_detail['device_id'] != $device_id) {
                $msg = "Perangkat yang anda gunakan tidak sesuai";
                return $this->respond(['status' => $status, 'message' => $msg], 400);
            }
      
            $key = getenv('JWT_SECRET');  
            $payload = array(
                "id" => trim($user['usr_id']),
                "username" => trim($user['username']),
                "iat" => strtotime("now"), //Time the JWT issued at
                "exp" => strtotime("+7 day", strtotime("now")), // Expiration time of token
            );
            $token = JWT::encode($payload, $key, 'HS256');
    
            $response = [
                'status'        => 'success',
                'message'       => 'Login Sukses',
                'token'         => $token,
                'token_expired' => date('Y-m-d H:i:s', strtotime("+7 day", strtotime("now"))),
                'user'          => $user_detail,
            ];
            $code = 200;
        } catch (Exception $e) {
            $response = [
                'status'        => 'failed',
                'message'       => 'Login '.$e->getMessage(),
            ];
            $code = 500;
        }
           
        return $this->respond($response, $code);
    }

    public function register() {
        $status = 'failed';
        $msg    = 'Failed to register user';
        $rules = [
            'username' => ['rules' => 'required|min_length[4]|max_length[255]|is_unique[app_user.username]', 'errors' => [
                'required' => 'Username tidak boleh kosong',
                'min_length' => 'Username harus diatas 8 karakter',
                'max_length' => 'Username harus dibawah 255 karakter',
                'is_unique' => 'Username sudah terdaftar',
            ]],
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[app_user.email]', 'errors' => [
                'required' => 'Email tidak boleh kosong',
                'min_length' => 'Email harus diatas 8 karakter',
                'max_length' => 'Email harus dibawah 255 karakter',
                'valid_email' => 'Email tidak valid',
                'is_unique' => 'Email sudah terdaftar',
            ]],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]', 'errors' => [
                'required' => 'Password tidak boleh kosong',
                'min_length' => 'Password harus diatas 8 karakter',
                'max_length' => 'Password harus dibawah 255 karakter',
            ]],
            'confirm_password'  => ['label' => 'confirm password', 'rules' => 'matches[password]', 'errors' => [
                'matches' => 'Konfirmasi password baru tidak sesuai',
            ]]
        ];
            
  
        if($this->validate($rules)) {
            $data = [
                'username'      => $this->request->getVar('username'),
                'email'         => $this->request->getVar('email'),
                'mobile_phone'  => $this->request->getVar('mobile_phone') ?? NULL,
                'fullname'      => $this->request->getVar('fullname') ?? NULL,
                'password'      => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'device_id'     => $this->request->getVar('device_id') ?? NULL,
                'acm_id'        => $this->request->getVar('acm_id') ?? 1,
                'agp_id'        => $this->request->getVar('agp_id') ?? 1,
                'arl_id'        => 2,
            ];

            $this->userModel->save($data);
            $status = 'success';
            $msg = 'User registered Successfully';

            return $this->respond(['status' => $status, 'message' => $msg], 200);
        } else {
            $response = [
                'status' => $status,
                'message' => implode(', ', $this->validator->getErrors()),
            ];

            return $this->respond($response , 400);
        }
    }

    public function logout() {
        $session = session();
        $session->destroy();
        return $this->respond(['status' => 'success', 'message' => 'OK'], 200);
    }
// 

// User
    public function getAllUsers() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];

        try {
            $data = $this->userModel->where('is_deleted', '0')->findAll();
            $status = 'success';
            $msg = '';
            $code = 200;
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => $data, 'message' => $msg], $code);
    }

    public function getDetailByUsername($username) {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            if($username == '') throw new Exception("User ID tidak ditemukan");

            $data = $this->userModel->getDetailByUsername($username);

            $status = 'success';
            $msg = '';
            $code = 200;
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => $data, 'message' => $msg], $code);
    }

    public function updateUserPassword() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];

        $rules = [
            'usr_id' => ['rules' => 'required', 'errors'=> [
                'required' => 'User ID tidak ditemukan'
            ]],
            'old_password' => ['rules' => 'required|min_length[8]|max_length[255],', 'errors' => [
                'required' => 'Password lama tidak boleh kosong',
                'min_length' => 'Password lama harus diatas 8 karakter',
                'max_length' => 'Password lama harus dibawah 255 karakter',
            ]],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]', 'errors' => [
                'required' => 'Password baru tidak boleh kosong',
                'min_length' => 'Password baru harus diatas 8 karakter',
                'max_length' => 'Password baru harus dibawah 255 karakter',
            ]],
            'confirm_password'  => [ 'label' => 'confirm password', 'rules' => 'matches[password]', 'errors' => [
                'matches' => 'Konfirmasi password baru tidak sesuai',
            ]],
        ];

        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            $old_pw = $this->request->getVar('old_password') ?? '';
            $new_pw = $this->request->getVar('password') ?? '';
            $device_id = $this->request->getVar('device_id') ?? '';

            if($this->validate($rules)) {
                // User validation
                $user = $this->userModel->where('usr_id', $usr)->first();
                if(is_null($user)) {
                    return $this->respond(['status' => $status, 'message' => $msg], 400);
                }

                if($device_id != $user['device_id']) {
                    $msg = "Perangkat yang anda gunakan tidak valid";
                    return $this->respond(['status' => $status, 'message' => $msg], 400);
                }
        
                $pwd_verify = password_verify($old_pw, $user['password']);
                if(!$pwd_verify) {
                    $msg = "Password yang anda masukkan salah";
                    return $this->respond(['status' => $status, 'message' => $msg], 400);
                }

                $data = [
                    'password'          => password_hash($new_pw, PASSWORD_DEFAULT),
                    'musr_id'           => $usr,
                    'mtime'             => date('Y-m-d H:i:s'),
                ];
    
                $r = $this->userModel->update($usr, $data);
                if($r) {
                    $status = 'success';
                    $msg = 'Sukses menyimpan data';
                    $code = 200;
                }
            } else {
                $response = [
                    'status' => $status,
                    'message' => implode(', ', $this->validator->getErrors()),
                ];
    
                return $this->respond($response , 400);
            }
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => [], 'message' => $msg], $code);
    }
//

// Attendance
    public function getAllAttendance() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("User ID tidak ditemukan");

            $type = $this->request->getVar('type') ?? '';
            $device_id = $this->request->getVar('device_id') ?? '';

            // User validation
            $user = $this->userModel->where('usr_id', $usr)->first();
            if(is_null($user)) {
                return $this->respond(['status' => $status, 'message' => $msg], 400);
            }

            if($device_id != $user['device_id']) {
                $msg = "Perangkat yang anda gunakan tidak valid";
                return $this->respond(['status' => $status, 'message' => $msg], 400);
            }

            $data = $this->attendanceModel->getAllAttendance($usr, $type);

            $status = 'success';
            $msg = '';
            $code = 200;
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => $data, 'message' => $msg], $code);
    }

    public function setAttendance() {
        $status = 'failed';
        $msg    = 'Gagal menyimpan data';
        $code   = 500;
        $data   = [];

        $rules = [
            'check_time' => ['rules' => 'required', 'errors' => [
                'required' => 'Waktu presensi tidak boleh kosong',
            ]],
            'latitude' => ['rules' => 'required', 'errors' => [
                'required' => 'Latitude tidak boleh kosong',
            ]],
            'longitude' => ['rules' => 'required', 'errors' => [
                'required' => 'Longitude tidak boleh kosong',
            ]],
        ];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("User ID tidak ditemukan");
            $device_id = $this->request->getVar('device_id') ?? '';

            if($this->validate($rules)) {
                // User validation
                $user = $this->userModel->where('usr_id', $usr)->first();
                if(is_null($user)) {
                    return $this->respond(['status' => $status, 'message' => $msg], 400);
                }
                
                if($device_id != $user['device_id']) {
                    $msg = "Perangkat yang anda gunakan tidak valid";
                    return $this->respond(['status' => $status, 'message' => $msg], 400);
                }

                $data = [
                    'type'          => $this->request->getVar('type') ?? '1',
                    'check_time'    => $this->request->getVar('check_time') ?? null,
                    'latitude'      => $this->request->getVar('latitude') ?? null,
                    'longitude'     => $this->request->getVar('longitude') ?? null,
                    'device_id'     => $this->request->getVar('device_id') ?? null,
                    'cusr_id'       => $usr,
                ];
    
                $r = $this->attendanceModel->insert($data, false);
                if($r) {
                    $status = 'success';
                    $msg = 'Sukses menyimpan data';
                    $code = 200;
                }
            } else {
                $response = [
                    'status' => $status,
                    'message' => implode(', ', $this->validator->getErrors()),
                ];
    
                return $this->respond($response , 400);
            }
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => [], 'message' => $msg], $code);
    }

    public function getAttendanceStat() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("User ID tidak ditemukan");

            $data = $this->attendanceModel->getAttendanceStat($usr);

            $status = 'success';
            $msg = '';
            $code = 200;
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => $data, 'message' => $msg], $code);
    }
//

// User Info
    public function getGroupData() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("User ID tidak ditemukan");

            $data = $this->groupModel->getGroupData($usr);

            $status = 'success';
            $msg = '';
            $code = 200;
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => $data, 'message' => $msg], $code);
    }
// 
}

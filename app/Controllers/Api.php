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
           
        $user = $this->userModel->where('username', $username)->first();
   
        if(is_null($user)) {
            return $this->respond(['status' => $status, 'message' => $msg], 401);
        }

        $pwd_verify = password_verify($password, $user['password']);
   
        if(!$pwd_verify) {
            $msg = "Password yang anda masukkan salah";
            return $this->respond(['status' => $status, 'message' => $msg], 401);
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
            'status' => 'success',
            'message' => 'Login Sukses',
            'token' => $token,
            "token_expired" => date('Y-m-d H:i:s', strtotime("+7 day", strtotime("now"))),
        ];
          
        return $this->respond($response, 200);
    }

    public function register() {
        $status = 'failed';
        $msg    = 'Failed to register user';
        $rules = [
            'username' => ['rules' => 'required|min_length[4]|max_length[255]|is_unique[app_user.username]'],
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[app_user.email]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirm_password'  => [ 'label' => 'confirm password', 'rules' => 'matches[password]']
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
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Input'
            ];

            return $this->respond($response , 409);
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
//

// Attendance
    public function getAllAttendance() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("Gagal: User ID tidak ditemukan");

            $type = $this->request->getVar('type') ?? '';
            $typecond = '';
            if($type != '') $typecond = "AND type = '$type'";

            $data = $this->attendanceModel->where("is_deleted = '0' AND cusr_id = '' $typecond")->orderBy("check_time","DESC")->findAll();

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
            'check_time' => ['rules' => 'required'],
            'latitude' => ['rules' => 'required'],
            'longitude' => ['rules' => 'required'],
            'device_id' => ['rules' => 'required'],
        ];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("Gagal: User ID tidak ditemukan");

            if($this->validate($rules)) {
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
                    'errors' => $this->validator->getErrors(),
                    'message' => 'Invalid Input'
                ];
    
                return $this->respond($response , 409);
            }
        } catch (Exception $e) {
            $msg .= ": ". $e->getMessage();
        }
        return $this->respond(['status' => $status, 'data' => [], 'message' => $msg], $code);
    }
//

// Radius and Location
    public function getGroupData() {
        $status = 'failed';
        $msg    = 'Gagal mendapatkan data';
        $code   = 500;
        $data   = [];
        
        try {
            $usr = $this->request->getVar('usr_id') ?? '';
            if($usr == '') throw new Exception("Gagal: User ID tidak ditemukan");

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

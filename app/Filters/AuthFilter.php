<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\AppUser;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        $key = getenv('JWT_SECRET');
        $header = $request->getHeader("Authorization");
        $token = null;

        // extract the token from the header
        if(!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
  
        // check if token is null or empty
        if(is_null($token) || empty($token)) {
            $body = ['status'=>'failed', 'message'=> 'Access denied'];
            $response->setJSON($body);
            $response->setStatusCode(401);
            return $response;
        }
  
        try {
            // $decoded = JWT::decode($token, $key, array("HS256"));
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            if (property_exists($decoded, 'username')) {
                // Check username dalam token
                $model = new AppUser();
                $usr = $decoded->username;
                $user = $model->where('username', $usr)->first();
                if (!$user) {
                    $body = ['status'=>'failed', 'message'=> 'Token tidak valid'];
                    $response->setJSON($body);
                    $response->setStatusCode(401);
                    return $response;
                }

                // Check expired dalam token
                $exp = $decoded->exp;
                if ($exp < time()) {
                    $body = ['status'=>'failed', 'message'=> 'Sesi token kadaluarsa'];
                    $response->setJSON($body);
                    $response->setStatusCode(401);
                    return $response;
                }
            } else {
                $body = ['status'=>'failed', 'message'=> 'Token tidak valid'];
                $response->setJSON($body);
                $response->setStatusCode(401);
                return $response;
            }
        } catch (Exception $ex) {
            $response = service('response');
            $body = ['status'=>'failed', 'message'=>'Akses ditolak'];
            $response->setJSON($body);
            $response->setStatusCode(401);
            return $response;
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

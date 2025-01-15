<?php

namespace App\Controllers;

use Lib\Responses;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Services\JwtService;

class UserController
{
    public function show()
    {
        $middleware = new AuthMiddleware();
        $middleware->handle();
        $user = new User();
        return $user->all();
    }
    public function index($request)
    {
        $response = new Responses('UserController-index');
        $user = new User();
        if(isset($request)){
            $request = json_decode($request, true);
            if(!isset($request['username']) || !isset($request['password'])){
                return $response->error_400();
            } else{
                $dataUser = $user->where('username', $request['username'])->where('password', md5($request['password']))->getFirst();
                if(!empty($dataUser)){
                    return json_encode([
                        'status' => 'success',
                        'data' => $dataUser
                    ]);
                } else {
                    return $response->error_200();
                }
            }
        }  else {
            return $response->error_400();
        }
    }
    public function store($request)
    {
        $response = new Responses('UserController-store');
        $user = new User();
        if(isset($request)){
            $request = json_decode($request, true);
            if(!isset($request['username']) || !isset($request['password']) || !isset($request['typeuser'])){
                return $response->error_400();
            } else{
                $request['status'] = 1;
                $jwt = new JwtService();
                $token = $jwt->getToken($request);
                $userCreate = $user->create([
                    'username' => $request['username'],
                    'password' => md5($request['password']),
                    'status' => $request['status'],
                    'typeuser' => $request['typeuser'],
                    'token' => $token
                ]);

                if($userCreate){
                    return json_encode([
                        'status' => 'success',
                        'data' => $userCreate,
                    ]);
                } else {
                    return $response->error_500();
                }
            }
        }  else {
            return $response->error_400();
        }
    }
}

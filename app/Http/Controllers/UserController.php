<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\MessageResource;

use Illuminate\Routing\Controller as BaseController;

use App\Models\User;
use App\Models\Order;

class UserController extends BaseController
{
    public $tokenValidSecond;

    public function __construct()
    {
        $this->tokenValidSecond = 3600;   // token 유효시간 설정
    }

    public function register(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['userNum'] = "";
        $result['msg'] = array();

        // 필수 값 확인
        if (($request->has('name')) && (!empty($request->name))) {
            $name = $request->name;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "name field is mandatory.");
        }
        if (($request->has('nickname')) && (!empty($request->nickname))) {
            $nickName = $request->nickname;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "nickname field is mandatory.");
        }
        if (($request->has('password')) && (!empty($request->password))) {
            $password = $request->password;
            $encrypted_password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "password field is mandatory.");
        }
        if (($request->has('phone')) && (!empty($request->phone))) {
            $phone = preg_replace('/-/', '', $request->phone);
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "phone field is mandatory.");
        }
        if (($request->has('email')) && (!empty($request->email))) {
            $email = $request->email;
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['result'] = "Fail";
                array_push($result['msg'], "email address is not valid.");
            }
        }

        // email이 로그인 아이디로 사용되기 때문에 email은 중복될 수 없다.
        $getUser = USER::where('email', $email)->first();
        if ($getUser) {
            $result['result'] = "Fail";
            array_push($result['msg'], "the email already has in DB.");
        }

        // 필수값이 있다면, 회원등록 진행
        if ($result['result'] != "Fail") {

            if (($request->has('gender')) && (!empty($request->gender))) {
                $gender = $request->gender;
            } else {
                $gender = NULL;
            }

            $userNUm = User::insertGetId([
                'name' => $name,
                'nickname' => $nickName,
                'password' => $encrypted_password,
                'phone' => $phone,
                'email' => $email,
                'gender' => $gender,
                'created_at' => Carbon::now()
            ]);


            $result['result'] = "Success";
            $result['userNum'] = $userNUm;
        }

        return json_encode($result);

    }

    public function login(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['token'] = "";
        $result['msg'] = array();

        // 필수 값 확인
        if (($request->has('email')) && (!empty($request->email))) {
            $email = $request->email;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "email field is mandatory.");
        }
        if (($request->has('password')) && (!empty($request->password))) {
            $password = $request->password;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "password field is mandatory.");
        }

        // 필수값이 있다면, 인증 체크
        if ($result['result'] != "Fail") {
            $getUser = USER::where('email', $email)->first();
            if ($getUser) {
                if (password_verify($password, $getUser->password)) {
                    // 이미 token이 생성되었는지 확인
                    if (empty($getUser->token)) {
                        // 인증 성공이므로 token 생성하기
                        $timestamp = time();
                        $token = hash_hmac('sha256', $getUser->name . $timestamp, $request->service);

                        // token 값과 생성일을 DB에 저장
                        User::where('email', $email)->update([
                            'token' => $token,
                            'token_created' => Carbon::now()
                        ]);
                    } else {
                        // token 유효시간 확인
                        $currentTime = Carbon::now();
                        $tokenDuration = $currentTime->diffInSeconds($getUser->token_created);

                        if ($this->tokenValidSecond > $tokenDuration) {
                            $token = $getUser->token;
                        } else {
                            // 인증 성공이므로 token 생성하기
                            $timestamp = time();
                            $token = hash_hmac('sha256', $getUser->name . $timestamp, $request->service);

                            // token 값과 생성일을 DB에 저장
                            User::where('email', $email)->update([
                                'token' => $token,
                                'token_created' => Carbon::now()
                            ]);
                        }
                    }

                    $result['result'] = "Success";
                    $result['token'] = $token;

                } else {
                    $result['result'] = "Fail";
                    array_push($result['msg'], "login is failed.");
                }
            } else {
                $result['result'] = "Fail";
                array_push($result['msg'], "login is failed.");
            }
        }
        return json_encode($result);
    }

    public function logout(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['msg'] = array();

        // 필수 값 확인
        if (($request->has('email')) && (!empty($request->email))) {
            $email = $request->email;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "email field is mandatory.");
        }

        // 필수값이 있다면, 인증 체크
        if ($result['result'] != "Fail") {
            $getUser = USER::where('email', $email)->first();
            if ($getUser) {
                // logout 처리
                User::where('email', $email)->update([
                    'token' => NULL,
                    'token_created' => NULL
                ]);

                $result['result'] = "Success";
            }
        }

        return json_encode($result);
    }

    public function userInfo(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['msg'] = array();
        $result['user'] = array();

        // 필수 값 확인
        if (($request->has('email')) && (!empty($request->email))) {
            $email = $request->email;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "email field is mandatory.");
        }
        if (($request->has('token')) && (!empty($request->token))) {
            $token = $request->token;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "token field is mandatory.");
        }

        // 필수값이 있다면, 인증 체크
        if ($result['result'] != "Fail") {
            $getUser = USER::where('email', $email)->first();
            if ($getUser) {
                // user token 값 체크
                if ($token==$getUser->token) {
                    // token 유효시간 확인
                    $currentTime = Carbon::now();
                    $tokenDuration = $currentTime->diffInSeconds($getUser->token_created);

                    if ($this->tokenValidSecond > $tokenDuration) {
                        $result['result'] = "Success";
                        $result['user'] = $getUser->toArray();

                        // 보안정보는 제거하고 전송
                        unset($result['user']['password']);
                        unset($result['user']['token']);
                        unset($result['user']['token_created']);
                    } else {
                        $result['result'] = "Fail";
                        array_push($result['msg'], "the token is expired.");
                    }
                } else {
                    $result['result'] = "Fail";
                    array_push($result['msg'], "the token is invalid.");
                }
            } else {
                $result['result'] = "Fail";
                array_push($result['msg'], "the user is not exist.");
            }
        }

        return json_encode($result);
    }

    public function getUsers(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['msg'] = array();
        $result['user'] = array();

        $rowPerPage = 10; // 한 페이지에 보여줄 row수 기본값
        $pageNumber = 1; // 조회할 페이지 기본값
        $name = "";
        $email = "";
        $whereString = "";

        // 한 페이지 몇 개를 보여주는가?
        if (($request->has('pagination')) && (!empty($request->pagination))) {
            $rowPerPage = $request->pagination;
        }
        // 몇 페이지를 조회?
        if (($request->has('page')) && (!empty($request->page))) {
            $pageNumber = $request->page;
        }

        // 필터 값 확인
        if (($request->has('name')) && (!empty($request->name))) {
            $name = $request->name;
            $whereString = "MATCH(name) AGAINST('".$name."*' in boolean mode)";
        }
        if (($request->has('email')) && (!empty($request->email))) {
            $email = $request->email;
            if (empty($whereString)) {
                $whereString = "MATCH(email) AGAINST('".$email."*' in boolean mode)";
            } else {
                $whereString = $whereString." AND MATCH(email) AGAINST('".$email."*' in boolean mode)";
            }
        }

        if (empty($whereString)) {
            $userInfo = USER::paginate($rowPerPage, ['*'], 'page', $pageNumber);
        } else {
            $userInfo = USER::whereRaw($whereString)->paginate($rowPerPage, ['*'], 'page', $pageNumber);
        }

        $tempArray = array();

        if (count($userInfo)>0) {
            $result['result'] = "Success";
            foreach($userInfo as $user) {
                $temp = $user->toArray();

                // 보안정보는 제거하고 전송
                unset($temp['password']);
                unset($temp['token']);
                unset($temp['token_created']);

                // 마지막 주문 정보
                $orderInfo = Order::where('user_num',$user->user_num)->orderBy('payment_created', 'desc')->limit(1)->first();
                $temp['order'] = array();
                if ($orderInfo) {
                    $temp['order'] = $orderInfo->toArray();
                    unset($temp['order']['user_num']);
                }
                array_push($tempArray, $temp);
            }
            $result['user'] = $tempArray;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "there is no record in users table.");
        }

        return json_encode($result);
    }

}

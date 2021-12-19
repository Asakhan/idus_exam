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

use App\Http\Controllers\RandomStringGenerator;

class OrderController extends BaseController
{
    public $generator;
    public $tokenLength;
    public $tokenValidSecond;

    public function __construct()
    {
        // Create new instance of generator class.
        $this->generator = new RandomStringGenerator;

        // Set token length.
        $this->tokenLength = 12;

        $this->tokenValidSecond = 3600;   // token 유효시간 설정
    }

    public function createOrder(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['orderNum'] = "";
        $result['msg'] = array();

        // 필수 값 확인
        if (($request->has('userNo')) && (!empty($request->userNo))) {
            $userNum = $request->userNo;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "userNo field is mandatory.");
        }
        if (($request->has('product')) && (!empty($request->product))) {
            $product = $request->product;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "product field is mandatory.");
        }
        if (($request->has('payment')) && (!empty($request->payment))) {
            $payment = $request->payment;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "payment field is mandatory.");
        }

        // 필수값이 있다면 주문 생성
        if ($result['result'] != "Fail") {
            $orderNum = $this->generator->generate($this->tokenLength);

            $order = new Order;
            $order->order_num = $orderNum;
            $order->user_num = $userNum;
            $order->product_name = $product;

            // 지출일
            $paymentDatetime = Carbon::createFromFormat('d/m/Y H:i:s', $payment);
            $order->payment_created = $paymentDatetime;

            $order->save();

            $result['result'] = "Success";
            $result['orderNum'] = $orderNum;
        }

        return json_encode($result);
    }

    public function getUserOrder(Request $request)
    {
        $result = array();
        $result['result'] = "";
        $result['order'] = array();
        $result['msg'] = array();

        // 필수 값 확인
        if (($request->has('userNo')) && (!empty($request->userNo))) {
            $userNum = $request->userNo;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "userNo field is mandatory.");
        }
        if (($request->has('token')) && (!empty($request->token))) {
            $token = $request->token;
        } else {
            $result['result'] = "Fail";
            array_push($result['msg'], "token field is mandatory.");
        }

        // 필수값이 있다면, 인증 체크
        if ($result['result'] != "Fail") {
            $getUser = USER::where('user_num', $userNum)->first();
            if ($getUser) {
                // user token 값 체크
                if ($token==$getUser->token) {
                    // token 유효시간 확인
                    $currentTime = Carbon::now();
                    $tokenDuration = $currentTime->diffInSeconds($getUser->token_created);

                    if ($this->tokenValidSecond > $tokenDuration) {
                        $result['result'] = "Success";

                        // 해당 사용자의 주문 조회
                        $orderInfo = Order::where('user_num',$userNum)->get();

                        $tempArray = array();
                        foreach($orderInfo as $order) {
                            $temp = $order->toArray();
                            unset($temp['user_num']);
                            array_push($tempArray, $temp);
                        }
                        $result['order'] = $tempArray;

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
}

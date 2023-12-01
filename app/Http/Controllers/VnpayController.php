<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class VnpayController extends Controller
{
    //
    public function vnpay(Request $request)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('vnpay-callback');
        $vnp_TmnCode = "8J7QFHB3"; 
        $vnp_HashSecret = "NVNVISYPNYWZLRDZAZKSNZWAYMNAANGI"; 

        $vnp_TxnRef = $request->order_code;
        $vnp_OrderInfo = "Thanh toan don hang";
        $vnp_OrderType = "billpayment";

        $vnp_Amount = $request->total_price * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00'
            ,
            'message' => 'success'
            ,
            'data' => $vnp_Url
        );
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        }
    }

    public function callback(Request $request)
    {
        $vnp_Amount = $request->input('vnp_Amount');
        $vnp_BankCode = $request->input('vnp_BankCode');
        $vnp_BankTranNo = $request->input('vnp_BankTranNo');
        $vnp_CardType = $request->input('vnp_CardType');
        $vnp_OrderInfo = $request->input('vnp_OrderInfo');
        $vnp_PayDate = $request->input('vnp_PayDate');
        $vnp_ResponseCode = $request->input('vnp_ResponseCode');
        $vnp_TmnCode = $request->input('vnp_TmnCode');
        $vnp_TransactionNo = $request->input('vnp_TransactionNo');
        $vnp_TransactionStatus = $request->input('vnp_TransactionStatus');
        $vnp_TxnRef = $request->input('vnp_TxnRef');
        $vnp_SecureHash = $request->input('vnp_SecureHash');

        $order = Order::where('order_code', $vnp_TxnRef)->first();

        switch ($vnp_TransactionStatus) {
            case '00':
               $dataUpdate = [
                    'status_order' => "Thanh toan thanh cong",
                ];
                break;
            case '24':
                $dataUpdate = [
                    'status_order' => "Khach hang huy thanh toan",
                ] ;
                break;
            case '11':
                $dataUpdate = [
                    'status_order' => "Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.n",
                ];
                break;
            case '13':
                $dataUpdate = [
                    'status_order' => "Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.",
                ];
                break;
            default:
                # code...
                break;
        }
        $update = $order->update($dataUpdate);
        
        return response()->json([
            'code' =>200,
            "message" => $dataUpdate
        ]);
    }
}

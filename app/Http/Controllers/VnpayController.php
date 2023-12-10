<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\QuanHuyen;
use App\Models\Tinhthanhpho;
use App\Models\XaPhuong;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VnpayController extends Controller
{
    //
    public function vnpay(Request $request)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = 'http://localhost:5173/payment';
        $vnp_TmnCode = "0BL8OXKS"; 
        $vnp_HashSecret = "HXQZMCAHKWRBHFEIUYVBRCBPEEUGAXPP"; 

        $vnp_TxnRef = $request->order_code;
        $vnp_OrderInfo = "Thanh toán đơn hàng";
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
        return response()->json([
            'status' => Response::HTTP_OK,
            'url_vnpay' => $vnp_Url,
        ], Response::HTTP_OK);
    }

    public function orderVNPay(Request $request)
    {

    try {
        $randomOrderCode = $request->order_code;
        $tinh  = Tinhthanhpho::find($request->tinh);
        $quan = QuanHuyen::find($request->quan);
        $xa = XaPhuong::find($request->xa);
        $district = $request->duong;
        
        $address = $district. " - " .  $xa->name . " - " . $quan->name . " - " . $tinh->name;
        $status = 2;
        // Tính tổng giá trị đơn hàng
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        $totalPrice = 0;

        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);
            if ($product->price_new == null) {
                $totalPrice += $cart->quantity * $product->price_old;
            } else {
                $totalPrice += $cart->quantity * $product->price_new;
            }
        }

        $shippingFee = $totalPrice >= 2000000 ? 0 : 40000;

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_price' => $totalPrice + $shippingFee, // Tổng giá trị đơn hàng cộng phí vận chuyển
            'order_code' => $randomOrderCode,
            'address' => $address,
            'status' => 2,
            'created_at' => Carbon::now(),
            "name" => $request->name,
            "phone" => $request->phone,
            "note" => $request->note,
            "payment_method" => 2
        ]);

        // Tạo chi tiết đơn hàng và cập nhật số lượng sản phẩm
        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $product->price_new ? $product->price_new : $product->price_old,
                'quantity' => $cart->quantity,
            ]);

            $product->update(['quantity' => $product->quantity - $cart->quantity]);
            $cart->delete();
        }

        // Trả về kết quả
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Đặt hàng thành công. Mã đơn hàng ' . $randomOrderCode,
        ], Response::HTTP_OK);

    } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in checkout',
                'error' => $error,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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

        // $order = Order::where('order_code', $vnp_TxnRef)->first();

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
        // $update = $order->update($dataUpdate);
        
        // return response()->json([
        //     'code' =>200,
        //     "message" => $dataUpdate
        // ]);
    }

    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
    public function orderMomo(Request $request)
    {

    try {
        $randomOrderCode = $request->order_code;
        $tinh  = Tinhthanhpho::find($request->tinh);
        $quan = QuanHuyen::find($request->quan);
        $xa = XaPhuong::find($request->xa);
        $district = $request->duong;
        
        $address = $district. " - " .  $xa->name . " - " . $quan->name . " - " . $tinh->name;
        $status = 2;
        // Tính tổng giá trị đơn hàng
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        $totalPrice = 0;

        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);
            if ($product->price_new == null) {
                $totalPrice += $cart->quantity * $product->price_old;
            } else {
                $totalPrice += $cart->quantity * $product->price_new;
            }
        }

        $shippingFee = $totalPrice >= 2000000 ? 0 : 40000;

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_price' => $totalPrice + $shippingFee, // Tổng giá trị đơn hàng cộng phí vận chuyển
            'order_code' => $randomOrderCode,
            'address' => $address,
            'status' => 2,
            'created_at' => Carbon::now(),
            "name" => $request->name,
            "phone" => $request->phone,
            "note" => $request->note,
            "payment_method" => 3
        ]);

        // Tạo chi tiết đơn hàng và cập nhật số lượng sản phẩm
        foreach ($carts as $cart) {
            $product = Product::findOrFail($cart->product_id);

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'price' => $product->price_new ? $product->price_new : $product->price_old,
                'quantity' => $cart->quantity,
            ]);

            $product->update(['quantity' => $product->quantity - $cart->quantity]);
            $cart->delete();
        }

        // Trả về kết quả
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Đặt hàng thành công. Mã đơn hàng ' . $randomOrderCode,
        ], Response::HTTP_OK);

    } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in checkout',
                'error' => $error,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function momo(Request $request) {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        $amount =  $request->total_price;
        // $orderId = time() ."";
        $orderId = $request->order_code;
        $redirectUrl = "http://localhost:5173/payment";
        $ipnUrl = "http://localhost:5173/payment";
        $extraData = "";

        $requestId = time() . "";
        $requestType = "payWithATM";
        // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array('partnerCode' => $partnerCode,
            'partnerName' => "Kính mắt ANNA",
            "storeId" => "Kính mắt ANNA",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature);
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json

        //Just a example, please check more in there

        // header('Location: ' . $jsonResult['payUrl']);
        // return redirect()->to($jsonResult['payUrl']);
        return response()->json([
            'status' => Response::HTTP_OK,
            'url_momo' => $jsonResult['payUrl'],
        ], Response::HTTP_OK);

    }
}

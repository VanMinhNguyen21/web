<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $cart = Cart::where('user_id', Auth::user()->id)->with("product")->get();
        $cartResponse= new CartResource($cart);

        return response()->json([
            'data' => $cartResponse,
        ],Response::HTTP_OK);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCartRequest $request)
    {
        //
        $product = Product::findOrFail($request->product_id);


        if(!is_null($product)){
            //check so luong
            if($request->quantity > $product->quantity){
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'So luong them vao gio hang vuot qua so luong trong kho',
                ],Response::HTTP_BAD_REQUEST);
            }
            // check ton tai trong gio hang
            $product_isset = Cart::where('user_id',Auth::user()->id)->where('product_id',$request->product_id)->get();
            if(count($product_isset)>0) {

                $product_isset = Cart::where('user_id',Auth::user()->id)->where('product_id',$request->product_id)->first();

                $product_isset->quantity = $product_isset->quantity +  $request->quantity;
                if($product->quantity < ($product_isset->quantity +  $request->quantity)) {
                    return response()->json([
                        "status" => 400,
                        "message" => "số lượng phải nhỏ hơn số trong kho"
                    ]);
                }
                $product_isset->save();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'Them san pham vao gio hang thanh cong',
                ],Response::HTTP_OK);
            }

            $dataUpdate = [
                'product_id' =>$request->product_id,
                'quantity' => $request->quantity,
                'user_id' => Auth::user()->id,
            ];
            $cart = Cart::create($dataUpdate);

            if($cart){
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'Them san pham vao gio hang thanh cong',
                ],Response::HTTP_OK);
            }
        }

        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => 'Them san pham vao gio hang bi loi',
        ],Response::HTTP_BAD_REQUEST);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $cart = Cart::findOrfail($id);
        $product = Product::find($cart->product_id);

        if($cart->quantity + $request->quantity > $product->quantity ){
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Tong san pham lon hon so luong trong kho!',
            ],Response::HTTP_BAD_REQUEST);
        }
        
        $cart->update(['quantity'=>$request->quantity]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Update san pham thanh cong!',
        ],Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $cart = Cart::findOrfail($id)->delete();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Xoa san pham kho gio hang thanh cong!',
        ],Response::HTTP_OK);
    }
}

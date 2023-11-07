<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishList;
use Exception;
use Illuminate\Http\Request;

class WishListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user_id = auth()->user()->id;
        $wish_list = WishList::with('product','user')->where('user_id',$user_id)->orderBy('id',"desc")->get();

        return response()->json([
            "status" => 200,
            'data' => $wish_list,
        ]) ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $product_id = $request->product_id;

        try {
            $product = Product::find($product_id);
            if(!$product) {
                return response()->json([
                    "status" => 400,
                    "message" => "san pham khong co trong he thong"
                ]);
            }
            $hasProduct = WishList::where('product_id',$product_id)->first();
            if($hasProduct) {
                return response()->json([
                    "status" => 400,
                    "message" => "san pham da trong danh sach yeu thich"
                ]);
            }

            $dataCreate = [
                "product_id" => $product_id,
                "user_id" => auth()->user()->id,
            ];

            $wish_list = new WishList();
            $wish_list->create($dataCreate);

            return response()->json([
                "status" => 200,
                "message" => "Them san pham yeu thich thanh cong"
            ]);
        }catch(Exception $err) {
            return response()->json([
                "status" => 400,
                "error" => $err
            ]);
        }
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
        $user_id = auth()->user()->id;
        $wish_list = WishList::where('user_id',$user_id)->where('product_id',$id)->first();
        $wish_list->delete();
        return response()->json([
            "status" => 200,
            'message' => "xóa sản phẩm khỏi danh sách yêu thích thành công",
        ]) ;

    }
}

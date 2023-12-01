<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TestController extends Controller
{
    //
    public function update(Request $request) {
       
    }

    public function uploadImageProduct($product_id, $images)
    {
        try{
            $pathImageProduct = $images->store('public/image_product');
            $arrayImage = explode('/', $pathImageProduct);
            array_shift($arrayImage);
            $imageProduct = implode('/', $arrayImage);
            $imageProductPath = asset('storage') . '/' . $imageProduct;
    
            $dataImageProduct = [
                'product_id' => $product_id,
                'image' => $imageProductPath,
            ];
    
            ProductImage::create($dataImageProduct);
        }catch(Exception $err) {
            dd($err);
        }
        
    }
}

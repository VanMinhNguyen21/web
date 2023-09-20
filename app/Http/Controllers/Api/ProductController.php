<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Console\Input\Input;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $shape = $request->shape_id;

        $material = $request->material_id;
        $min_price = $request->min_price;
        $max_price = $request->max_price;

        $product = Product::query();

        $response = $product->when(!empty($shape), function ($q) use ($shape) {
                $shape_id = explode(',', $shape);
                return $q->whereIn('shape_id', $shape_id);
            })
            ->when(!empty($material), function ($q) use ($material) {
                $material_id = explode(',', $material);
                return $q->whereIn('shape_id', $material_id);
            })
            ->when(!empty($arrange_price), function ($q) use ($min_price, $max_price) {
                return $q->whereBetween('price_new', [$min_price, $max_price]);
            })
            ->with('category', 'material', 'shape', 'imageProduct')->where('status',STATUS_ACTIVE)->get();

        return response()->json([
            'data' => $response,
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        try {
            $data = $request->all();

            $pathThumbnail = $request->file('thumbnail')->store('public/product');
            $array = explode('/', $pathThumbnail);
            array_shift($array);
            $image = implode('/', $array);
            $imagePath = asset('storage') . '/' . $image;
            $dataCreate = [
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'thumbnail' => $imagePath,
                'price_new' => $request->price_new,
                'price_old' => $request->price_old,
                'quantity' => $request->quantity,
                'color' => $request->color,
                'material_id' => $request->material_id,
                'shape_id' => $request->shape_id,
                'status' => $request->status
            ];

            $result = Product::create($dataCreate);

            if ($request->hasFile('image_product')) {
                $images = $request->file('image_product');
                $this->uploadImageProduct($result->id, $images);
            }

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Create product successfully!'
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in create product',
                'error' => $error,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
        // $product = Product::findOrFail($id);

        $response = Product::with('category', 'shape', 'material', 'imageProduct')->findOrFail($id);
        $responseResource =  new ProductResource($response);

        return response()->json([
            'status' => 200,
            'data' => $responseResource,
        ]);
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
        $data = $request->all();
        $response = Product::with('category', 'shape', 'material', 'imageProduct')->findOrFail($id);
        // $responseResource =  new ProductResource($response);
        $imagePath = "";
        if ($request->hasFile('thumbnail')) {
            $pathThumbnail = $request->file('thumbnail')->store('public/product');
            $array = explode('/', $pathThumbnail);
            array_shift($array);
            $image = implode('/', $array);
            $imagePath = asset('storage') . '/' . $image;
        }
        $dataUpdate = [
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'thumbnail' => !empty($imagePath) ? $imagePath : $response->thumbnail,
            'price_new' => $request->price_new,
            'price_old' => $request->price_old,
            'quantity' => $request->quantity,
            'color' => $request->color,
            'material_id' => $request->material_id,
            'shape_id' => $request->shape_id,
            'status' => $request->status
        ];


        $response->update($dataUpdate);
        $ProductResource = new ProductResource($response);

        if ($ProductResource) {
            return response()->json([
                'status' => 200,
                'message' => 'Update product successfully!',
                'data' => $ProductResource,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Update category fail!',
            ], Response::HTTP_BAD_REQUEST);
        }
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
    }

    public function uploadImageProduct($product_id, $images)
    {
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
    }

    public function updateStatusProduct(Request $request){
        $product = Product::findOrfail($request->product_id);
        switch($request->status) {
            case(STATUS_ACTIVE):
                $product->update([
                    'status' => STATUS_ACTIVE
                ]); 
                $msg = 'Update status product successfully!';
                break;
            case(STATUS_LOCK):
                
                $product->update([
                    'status' => STATUS_LOCK
                ]); 
                $msg = 'Update status product successfully!';
                break;

            default:
                $msg = 'Something went wrong.';   
        }
        return response()->json([
            'message' => $msg
        ]);
    }
}

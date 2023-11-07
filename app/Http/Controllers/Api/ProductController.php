<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Exception;
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
        $category = $request->category;
        $arrange_price = [];
        if(!empty($min_price) && !empty($max_price)) {
            $arrange_price = [$min_price,$max_price];

        }
        $product = Product::query();

        $response = $product->when(!empty($shape), function ($q) use ($shape) {
            return $q->where('shape_id', $shape);
        })
        ->when(!empty($material), function ($q) use ($material) {
            return $q->where('material_id', $material);
        })
        ->when(!empty($arrange_price), function ($q) use ($arrange_price) {
            if(!empty($q->price_new)) {
                return $q->whereBetween('price_new', $arrange_price);
            }else{
                return $q->whereBetween('price_old', $arrange_price);
            }
        })->when(!empty($category), function ($q) use ($category) {
            return $q->where('category_id', $category);
        })
        ->with('category', 'supplier', 'material', 'shape', 'imageProduct')->orderBy('id',"desc")->get();

        return response()->json([
            'data' => $response,
        ], Response::HTTP_OK);
    }

    public function getProductForAdmin(Request $request)
    {
        //
        $shape = $request->shape_id;

        $material = $request->material_id;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $category = $request->category;

        $product = Product::query();
        $arrange_price = [];
        if(!empty($min_price) && !empty($max_price)) {
            $arrange_price = [$min_price,$max_price];
        }

        $response = $product->when(!empty($shape), function ($q) use ($shape) {
            return $q->where('shape_id', '. $shape.');
        })
        ->when(!empty($material), function ($q) use ($material) {
            return $q->where('material_id', ". $material. ");
        })
        ->when(!empty($arrange_price), function ($q) use ($arrange_price) {
            if(!empty($q->price_new)) {
                return $q->whereBetween('price_new', $arrange_price);
            }else{
                return $q->whereBetween('price_old', $arrange_price);
            }
        })->when(!empty($category), function ($q) use ($category) {
            return $q->where('category_id', $category);
        })
        ->with('category', 'supplier', 'material', 'shape', 'imageProduct')->orderBy('id','desc')->get();

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
    public function store(StoreProductRequest $request)
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
                foreach($request->file('image_product') as $file) {
                    $images = $request->file('image_product');
                    $this->uploadImageProduct($result->id, $file);
                }
               
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

        $response = Product::with('category',"supplier", 'shape', 'material', 'imageProduct')->findOrFail($id);
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
        dd($request->all());
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

        if($request->hasFile("image_product")) {
            dd("a");
            $productImages = ProductImage::where("product_id",$id);
            foreach($productImages as $productImage)
            {
                $productImage->delete();
            }

                foreach($request->file('image_product') as $file) {
                    $images = $request->file('image_product');
                    $this->uploadImageProduct($id, $file);
                }
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
            'status' => $request->status ? $request->status : $response->status
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

    public function updateImageProduct (Request $request) {
        $data = $request->all();
        $id = $request->product_id;
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
        if($request->hasFile("image_product")) {
            $productImages = ProductImage::where("product_id",$id)->get();

            if($productImages) {
                foreach($productImages as $productImage)
                {
                    $productImage->delete();
                }
            }

            foreach($request->file('image_product') as $file) {
                $this->uploadImageProduct($id, $file);
            }
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
            'status' => $request->status ? $request->status : $response->status
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

    public function changeImageProduct($product_id,$image_products) {
        try{
            

            return response()->json([
                "status" => 200,
                "message" => 'update images successfully',
            ]);
        }catch(Exception $err)
        {
            return response()->json([
                "status" => 400,
                "message" => 'error when update image ',
                'err' => $err
            ]);
        }
        
    }

    public function getProductHasPriceNew(Request $request) {
        $shape = $request->shape_id;

        $material = $request->material_id;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $category = $request->category;
        $arrange_price = [];
        if(!empty($min_price) && !empty($max_price)) {
            $arrange_price = [$min_price,$max_price];

        }
        $product = Product::query();

        $response = $product->when(!empty($shape), function ($q) use ($shape) {
            return $q->where('shape_id', $shape);
        })
        ->when(!empty($material), function ($q) use ($material) {
            return $q->where('material_id', $material);
        })
        ->when(!empty($arrange_price), function ($q) use ($arrange_price) {
            if(!empty($q->price_new)) {
                return $q->whereBetween('price_new', $arrange_price);
            }else{
                return $q->whereBetween('price_old', $arrange_price);
            }
        })->when(!empty($category), function ($q) use ($category) {
            return $q->where('category_id', $category);
        })
        ->with('category', 'supplier', 'material', 'shape', 'imageProduct')->where('price_new', "!=", null)->orderBy('id',"desc")->get();

        return response()->json([
            'data' => $response,
        ], Response::HTTP_OK);
    }
    
}

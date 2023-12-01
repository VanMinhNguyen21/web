<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $supplierName = $request->supplierName;
        $query = Supplier::query();
        $categories = $query->when(!empty($supplierName), function ($q) use ($supplierName) {
            $q->where('name', 'LIKE', "%{$supplierName}%");
        })->with('product')->orderBy("id","DESC")->get();

        $supplierResource =  SupplierResource::collection($categories)->response()->getData();

        return $supplierResource;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSupplierRequest $request)
    {
        //
        try {
            $data = $request->all();
            $result = Supplier::create($data);

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Create supplier successfully!'
                ]
            );

        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in create supplier',
                'error' => $error,
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
        $response = Supplier::findOrFail($id);
        $responseResource =  new SupplierResource($response);

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
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => ['required','email',Rule::unique('supplier','email')->ignore($supplier->id)],
            'address' => 'required',
            'telephone' => 'required'
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'address.required' => 'The name field is required.',
            'telephone.required' => 'The telephone field is required.'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $supplier->update($data);
            $categoryResource = new SupplierResource($supplier);
            if($categoryResource){
                return response()->json([
                    'status' => 200,
                    'message' => 'Update supplier successfully!',
                    'data' => $categoryResource,
                ],Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Update supplier fail!',
                ],Response::HTTP_BAD_REQUEST);
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
        // $supplier = Supplier::findOrFail($id);

        // $supplier->update(['deleted_at'=>Carbon::now()]);

        // return response()->json([
        //     'message' => 'delete supplier successful',
        // ],Response::HTTP_OK);

        Supplier::findOrFail($id)->delete();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Delete suppplier successfully!',
        ]);
    }
}

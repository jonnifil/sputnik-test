<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): JsonResponse
    {
         $models = $this->repository->getPaginateList($request);

         return $this->respondWithCollection(new ProductCollection($models));
    }

    public function show(Product $product): JsonResponse
    {
        return $this->respondWithItem(new ProductResource($product));
    }


    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->respondCreateItem(new ProductResource($product));
    }


    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->all());
        $product->refresh();

        return $this->respondUpdateItem(new ProductResource($product));
    }


    public function destroy(Product $product)
    {
         $product->delete();

         return $this->respondDeleteItem();
    }
}

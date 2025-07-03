<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\StorePriceRequest;
use App\Http\Requests\UpdatePriceRequest;
use App\Http\Resources\PriceCollection;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Repositories\PriceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends ApiController
{
    public function __construct(PriceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): JsonResponse
    {
         $models = $this->repository->getPaginateList($request);

         return $this->respondWithCollection(new PriceCollection($models));
    }

    public function show(Price $price): JsonResponse
    {
        return $this->respondWithItem(new PriceResource($price));
    }


    public function store(StorePriceRequest $request)
    {
        $price = Price::create($request->validated());

        return $this->respondCreateItem(new PriceResource($price));
    }


    public function update(UpdatePriceRequest $request, Price $price)
    {
        $price->update($request->all());
        $price->refresh();

        return $this->respondUpdateItem(new PriceResource($price));
    }


    public function destroy(Price $price)
    {
         $price->delete();

         return $this->respondDeleteItem();
    }
}

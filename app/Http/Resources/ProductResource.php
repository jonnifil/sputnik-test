<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currency = $request->get("currency", 'RUB');
        /** @var CurrencyService $service */
        $service = resolve(CurrencyService::class);
        /** @var Product $this */
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $service->convertPrintPrice($this->price, $currency),
        ];
    }
}

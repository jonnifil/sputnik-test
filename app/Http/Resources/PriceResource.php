<?php

namespace App\Http\Resources;

use App\Models\Price;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
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
        /** @var Price $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $service->convertPrintPrice($this->price, $currency),
        ];
    }
}

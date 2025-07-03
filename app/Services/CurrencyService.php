<?php

namespace App\Services;

class CurrencyService
{
    public function convertPrintPrice($price, $currency = 'RUB'): string
    {
        return match ($currency) {
            'USD' => $this->getDollarPrintPrice($price),
            'EUR' => $this->getEuroPrintPrice($price),
            default => $this->getRubPrintPrice($price),
        };
    }

    public function getRubPrintPrice(int $price): string
    {
        return number_format($price, 0, ',', ' ') . ' ₽';
    }

    public function getEuroPrintPrice(int $price): string
    {
        $rate = $this->getRate('EUR');
        $price = round($price / $rate, 2);

        return "€" . number_format($price, 2, ',', ' ');
    }

    public function getDollarPrintPrice(int $price): string
    {
        $rate = $this->getRate('USD');
        $price = round($price / $rate, 2);

        return "$" . number_format($price, 2, ',', ' ');
    }

    protected function getRate(string $currency): float
    {
        return match ($currency) {
            'USD' => 90, //todo: получение актуальных курсов валют
            'EUR' => 100,
            default => 1
        };
    }
}

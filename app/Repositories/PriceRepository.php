<?php
namespace App\Repositories;

use App\Models\Price;
use Illuminate\Support\Facades\Request;

class PriceRepository extends BaseRepository
{
    /**
     * PriceRepository constructor.
     * @param Price $model
     */
    public function __construct(Price $model)
    {
        parent::__construct(Request::instance());
        $this->model = $model;
    }
}

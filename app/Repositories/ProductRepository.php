<?php
namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Request;

class ProductRepository extends BaseRepository
{
    /**
     * ProductRepository constructor.
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        parent::__construct(Request::instance());
        $this->model = $model;
    }
}

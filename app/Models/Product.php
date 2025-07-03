<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $title
 * @property integer $price
 */
class Product extends Model
{
    protected $fillable = [
        'title',
        'price',
    ];
}

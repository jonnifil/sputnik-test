<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $price
 */
class Price extends Model
{
    protected $fillable = [
        'price',
        'name'
    ];
}

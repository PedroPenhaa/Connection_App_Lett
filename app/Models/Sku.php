<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',  'external_id',
        'brick_id', 'brand_id',
        'ean', 'retailer_sku_match',
        'content'
    ];
}

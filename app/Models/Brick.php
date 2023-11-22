<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brick extends Model
{
    use HasFactory;

    protected $fillable = ['name',  'external_id', 'class_id'];
}

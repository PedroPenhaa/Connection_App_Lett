<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    // Especifica quais atributos podem ser preenchidos/Alterados no banco.
    protected $fillable = ['name',  'external_id', 'segment_id'];
}

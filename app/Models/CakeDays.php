<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CakeDays extends Model
{
    use HasFactory;
    protected $fillable = ['developer_names','no_of_cakes', 'cake_date','cake_type'];
}

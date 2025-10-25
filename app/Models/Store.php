<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}

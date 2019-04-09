<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'slug', 'image', 'created_by'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

}

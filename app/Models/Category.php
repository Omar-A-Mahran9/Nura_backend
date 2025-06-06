<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['name'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d'
    ];
    public function getNameAttribute()
    {
        return $this->attributes['name_'. getLocale()];
    }
    public function articles()
    {
        return $this->belongsToMany(Articles::class, 'article_category', 'category_id', 'article_id');
    }
    
 

}

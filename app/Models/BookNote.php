<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookNote extends Model
{
    use HasFactory;
    protected $table = 'book_notes';

    protected $guarded = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class); // Ensure you have a Vendor model
    }
        public function book()
        {
            return $this->belongsTo(Book::class);
        }

   }

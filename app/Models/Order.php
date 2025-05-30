<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function live()
    {
        return $this->belongsTo(Live::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultaion::class, 'consultaion_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function consultaionType()
    {
        return $this->belongsTo(ConsultaionType::class, 'consultaion_type_id');
    }

    public function consultaionSchedual()
    {
        return $this->belongsTo(ConsultaionSchedual::class, 'consultaion_schedual_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

}

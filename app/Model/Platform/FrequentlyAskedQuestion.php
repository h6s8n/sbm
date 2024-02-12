<?php

namespace App\Model\Platform;

use Illuminate\Database\Eloquent\Model;

class FrequentlyAskedQuestion extends Model
{
    protected $table='FAQ';
    protected $fillable=['questionable_type','questionable_id','question','answer'];
    public function questionable()
    {
        return $this->morphTo();
    }
}

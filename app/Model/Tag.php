<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded=['id'];

    public function searchable()
    {
        return $this->morphTo();
    }
}

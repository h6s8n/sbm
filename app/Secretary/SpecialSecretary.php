<?php

namespace App\Secretary;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SpecialSecretary extends Model
{
    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StarRate extends Model
{
    protected $fillable=['overall','votable_type','votable_id',
        'user_id','comment','doctor_id','quality','cost','behaviour','flag','reply'];

    public function votable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'votable_id')->where('votable_type', 'App\User');
    }
}

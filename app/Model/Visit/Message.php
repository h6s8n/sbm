<?php

namespace App\Model\Visit;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * Fields that are mass assignable
     *
     * @var array
     */
    protected $fillable = ['message','reply_to'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

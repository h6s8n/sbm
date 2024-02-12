<?php

namespace App\Model\Visit;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{

    protected $appends = ["type_message", "type"];

    public function getTypeMessageAttribute()
    {
        if ($this->message && !$this->file)
            return 'text';
        return 'file';
    }

    public function getTypeAttribute()
    {
        return 'prescriptionFile';
    }
}

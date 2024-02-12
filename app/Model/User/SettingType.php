<?php

namespace App\Model\User;

use App\Enums\UsersSetting;
use Illuminate\Database\Eloquent\Model;

class SettingType extends Model
{
    protected $appends=['name'];

    public function getTypeNameAttribute(): string
    {
        return UsersSetting::toKey($this->setting_type_id);
    }
}

<?php

namespace App\Model\Doctor;

use App\Model\Language;
use App\Model\Tag;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    public $timestamps=false;
    protected $fillable=['name','slug','svg_url','description','brief','priority','language_id'];

    public function Users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,UserSpecialization::class);
    }

    public function SearchArea()
    {
        return $this->morphOne(Tag::class,'searchable');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

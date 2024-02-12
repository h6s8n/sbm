<?php


namespace App\Repositories\v2\Profile\Doctor;


interface ProfileInterface
{
    public function get($value,$type,$with=null);

    public function update($data);
}

<?php


namespace App\Repositories\v2\Doctor;


interface DoctorInterface
{
    public function update($data);

    public function find($id);
    public function SyncSearchArea($doctor,$data);
}

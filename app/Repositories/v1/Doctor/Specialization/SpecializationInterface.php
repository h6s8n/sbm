<?php


namespace App\Repositories\v1\Doctor\Specialization;


use App\Model\Doctor\Specialization;

interface SpecializationInterface
{
    public function all($paginate = null);

    public function store($data);

    public function OrderBy($collection,$value,$type);

    public function update(Specialization $model,$data);

    public function assignDoctor($data);

    public function withUsers();

    public function withCalender();

    public function delete(Specialization $sp);

    public function search($filter);

    public function paginate($collection,$paginate);
}

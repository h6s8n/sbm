<?php

namespace App\Traites;

trait UsersTypeTraites
{
    private function ConvertTypeNameToId($name)
    {
        $data = [
            'Doctor' => 1,
            'Patient' => 2
        ];
        return $data[$name];
    }
    private function ConvertTypeIdToName($id)
    {
        $data = [
            1 => 'Doctor',
            2 => 'Patient'
        ];
        return $data[$id];
    }
}

<?php

namespace App\Traites;

use App\Enums\CurrencyEnum;

trait CurrencyChangeTrait
{
    public function ConvertRialTo($code,$price)
    {
        switch ($code){
            case CurrencyEnum::EUR: {
               return (($price)/300000) + 5;
               break;
            }
        }
    }
}

?>

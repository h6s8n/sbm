<?php

namespace App\Services\Gateways;


interface GatewayInterface
{
    public function setPayPrice($pay_price);

    public function setCallbackUrl($url);

    public function setToken($token);

    public function verify();

    public function pay($token,$amount);
}

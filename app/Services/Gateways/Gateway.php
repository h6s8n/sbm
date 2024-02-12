<?php

namespace App\Services\Gateways;


use App\Services\Gateways\GateWayInterface;
use App\Services\Gateways\src\Zibal;

abstract class Gateway
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @param GateWayInterface|string $gateway
     */
    public function __construct($gateway)
    {
        $this->handleGateway($gateway);
    }

    public function setPayPrice($pay_price)
    {
        $this->gateway->setPayPrice($pay_price);
        return $this;
    }

    public function setCallbackUrl($url)
    {
        $this->gateway->setCallbackUrl($url);
        return $this;
    }

    public function setToken($token)
    {
        $this->gateway->setToken($token);
        return $this;
    }

    public function verify()
    {
        return $this->gateway->verify();
    }

    public function pay($token,$amount)
    {
        return $this->gateway->pay($token,$amount);
    }

    public function handleGateway($gateway): void
    {
        if ($gateway instanceof GateWayInterface) {
            $this->gateway = $gateway;
        } elseif (is_string($gateway)) {
            switch (strtoupper($gateway)){
                case 'VANDAR':
                    $this->gateway = new Vandar;
                    break;
                case 'ZIBAL':
                    $this->gateway = new Zibal;
                    break;
                default:
                    abort(404);
            }
        } else {
            abort(404);
        }

    }
}

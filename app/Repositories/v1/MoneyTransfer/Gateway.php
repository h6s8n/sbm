<?php


namespace App\Repositories\v1\MoneyTransfer;


class Gateway
{
    private $gateway;

    public function __construct(GateWayInterface $gateWay)
    {
        $this->gateway=$gateWay;
    }

    public function transfer()
    {
        return $this->gateway->transfer();
    }
}

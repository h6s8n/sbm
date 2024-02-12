<?php

namespace App\Http\Controllers\Api\v1\Visit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\v1\Rate\RateInterface;
use Illuminate\Http\Response;

class RateController extends Controller
{
    private $rate;

    public function __construct(RateInterface $rate)
    {
        $this->rate=$rate;
    }

    /**
     * Store a new rate
     * @param $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'token'=>'required',
            'rate'=>'max:5',
            'type'=>'required',
        ]);
       $response = $this->rate->store($request->all());
       if ($response['status'])
           return success_template($response['data']);
       return error_template($response['message']);
    }
}

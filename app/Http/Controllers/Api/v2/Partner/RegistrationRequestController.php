<?php

namespace App\Http\Controllers\Api\v2\Partner;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Partners\RegistrationRequest;
use Exception;

class RegistrationRequestController extends Controller
{

    /**
     * Store a new rate
     * @param $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $ValidData = $this->validate($request,[
            'applicant_name' => 'required|string',
            'partner_name' => 'required|string',
            'applicant_post' => 'required|string',
            'total_doctors' => 'required|numeric',
            'phone' => 'required|digits_between:10,11',
        ], [
            'applicant_name.required' => 'نام درخواست کننده الزامی است.',
            'partner_name.required' => 'نام مرکز الزامی است.',
            'applicant_post.required' => 'سمت درخواست کننده الزامی است.',
            'total_doctors.required' => 'تعداد پزشکان مرکز الزامی است.',
            'total_doctors.numeric' => 'تعداد پزشکان مرکز باید عدد باشد.',
            'phone.required' => 'شماره تماس الزامی است.',
            'phone.digits_between' => 'شماره تماس نامعتبر.',
        ]);

        try {
            $registrationRequest = RegistrationRequest::create($request->all());
            if ($registrationRequest instanceof RegistrationRequest)
                $response = [
                    'status' => true,
                    'data' => $registrationRequest
                ];
        } catch (Exception $ex) {
            $response = [
                'status' => false,
                'message' => $ex->getMessage()
            ];
        }

        if ($response['status'])
            return success_template($response['data']);
        return error_template($response['message']);
    }
}

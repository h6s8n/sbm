<?php

namespace App\Http\Controllers\Api\v2\FileManager;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Partners\RegistrationRequest;
use Exception;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{

    public function show($file)
    {
        try{
            $file = Storage::disk('datadisk')->path('statics-public/'.$file);
            if ($file)
                return response()->file($file);}
        catch (Exception $e){
            return abort(404);
        }
    }
}

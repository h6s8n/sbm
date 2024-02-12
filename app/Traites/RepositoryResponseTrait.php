<?php


namespace App\Traites;


trait RepositoryResponseTrait
{
    private function SuccessResponse($object)
    {
        $response = new \stdClass();
        $response->status = true;
        $response->object = $object;
        return $response;
    }
    private function ErrorTemplate($message)
    {
        $response = new \stdClass();
        $response->status = false;
        $response->message = $message;
        return $response;
    }
}

<?php

namespace BraspagSdk\Contracts\Pagador;

class SaleResponse
{
    public $HttpStatus;

    public $Error;

    public $ErrorDescription;

    public static function fromJson($json)
    {
        $object = json_decode($json);
        $response = new SaleResponse();
        $response->populate($object);
        return $response;
    }

    public function populate(\stdClass $data)
    {
        $dataProps = get_object_vars($data);

//        if (isset($dataProps['error']))
//            $this->Error = $dataProps['error'];
    }
}
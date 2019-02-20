<?php

namespace BraspagSdk\Contracts\Pagador;

class SaleResponse
{
    public $HttpStatus;

    public $Error;

    public $ErrorDescription;

    public $MerchantOrderId;

    public $Customer;

    public $Payment;

    public static function fromJson($json)
    {
        $object = json_decode($json);
        $response = new SaleResponse();
        $response->populate($object);
        return $response;
    }

    public function populate(\stdClass $data)
    {
        foreach($data as $key => $val)
            if(property_exists(__CLASS__,$key))
                $this->$key = $val;
    }
}
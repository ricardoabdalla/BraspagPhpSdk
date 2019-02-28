<?php

namespace BraspagSdk\Contracts\Pagador;

class PaymentIdResponse
{
    public $HttpStatus;

    // List<PaymentIdData>
    public $Payments;

    public function __construct()
    {
        $this->Payments = array();
    }

    public static function fromJson($json)
    {
        $response = new PaymentIdResponse();
        $jsonArray = json_decode($json);

        foreach ($jsonArray as $key => $val)
            if (property_exists(__CLASS__, $key))
                $response->$key = $val;

        return $response;
    }
}
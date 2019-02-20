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

    public function __construct()
    {
        $this->Customer = new CustomerData();
        $this->Payment = new PaymentDataRequest();
    }

    public static function fromJson($json)
    {
        $response = new SaleResponse();
        $jsonArray = json_decode($json);

        foreach ($jsonArray as $key => $val)
            if (property_exists(__CLASS__, $key))
                $response->$key = $val;

        return $response;
    }
}
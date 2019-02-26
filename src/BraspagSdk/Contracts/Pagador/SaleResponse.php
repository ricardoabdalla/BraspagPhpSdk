<?php

namespace BraspagSdk\Contracts\Pagador;

class SaleResponse
{
    public $Customer;

    public $ErrorDataCollection;

    public $HttpStatus;

    public $MerchantOrderId;

    public $Payment;

    public function __construct()
    {
        $this->Customer = new CustomerData();
        $this->ErrorDataCollection = array();
        $this->Payment = new PaymentDataResponse();
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
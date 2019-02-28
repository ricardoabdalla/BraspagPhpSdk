<?php

namespace BraspagSdk\Contracts\Pagador;

class RecurrentDataResponse
{
    public $HttpStatus;
    
    // CustomerData
    public $Customer;
    
    // RecurrentPaymentData
    public $RecurrentPayment;

    public function __construct()
    {
        $this->Customer = new CustomerData();
        $this->RecurrentPayment = new RecurrentPaymentData();
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
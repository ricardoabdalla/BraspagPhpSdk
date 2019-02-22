<?php

namespace BraspagSdk\Contracts\Pagador;

class SaleRequest
{
    public $MerchantOrderId;

    public $Customer;

    public $Payment;

    /**
     * SaleRequest constructor.
     */
    public function __construct()
    {
        $this->Customer = new CustomerData();
        $this->Payment = new PaymentDataRequest();
    }
}
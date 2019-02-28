<?php

namespace BraspagSdk\Contracts\Pagador;

class CaptureRequest
{
    public $Amount;
    
    public $ServiceTaxAmount;
    
    public $PaymentId;
}
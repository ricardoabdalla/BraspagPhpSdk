<?php

namespace BraspagSdk\Contracts\CartaoProtegido;

class MerchantCredentials
{
    public $MerchantKey;

    /**
     * MerchantCredentials constructor.
     * @param $MerchantKey
     */
    public function __construct($MerchantKey)
    {
        $this->MerchantKey = $MerchantKey;
    }
}
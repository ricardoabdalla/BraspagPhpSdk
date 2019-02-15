<?php

namespace BraspagSdk\Contracts\Pagador;

class MerchantCredentials
{
    public $MerchantId;

    public $MerchantKey;

    /**
     * MerchantCredentials constructor.
     * @param $MerchantId
     * @param $MerchantKey
     */
    public function __construct($MerchantId, $MerchantKey)
    {
        $this->MerchantId = $MerchantId;
        $this->MerchantKey = $MerchantKey;
    }
}
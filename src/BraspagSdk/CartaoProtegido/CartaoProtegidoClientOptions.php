<?php

namespace BraspagSdk\CartaoProtegido;

use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Contracts\CartaoProtegido\MerchantCredentials;

class CartaoProtegidoClientOptions extends ClientOptions
{
    public $credentials;

    /**
     * CartaoProtegidoClientOptions constructor.
     * @param $credentials
     * @param $Environment
     */
    public function __construct(MerchantCredentials $credentials, $Environment)
    {
        $this->credentials = $credentials;
        $this->Environment = $Environment;
    }
}
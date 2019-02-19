<?php

namespace BraspagSdk\Pagador;

use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;

class PagadorClientOptions extends ClientOptions
{
    public $credentials;
    public $Environment;

    /**
     * PagadorClientOptions constructor.
     * @param $credentials
     * @param $Environment
     */
    public function __construct(MerchantCredentials $credentials, $Environment)
    {
        $this->credentials = $credentials;
        $this->Environment = $Environment;
    }
}
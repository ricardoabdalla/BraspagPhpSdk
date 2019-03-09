<?php

namespace BraspagSdk\Pagador;

use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Contracts\Pagador\MerchantCredentials;

class PagadorClientOptions extends ClientOptions
{
    public $Credentials;

    /**
     * PagadorClientOptions constructor.
     * @param $credentials
     * @param $environment
     */
    public function __construct(MerchantCredentials $credentials, $environment = null)
    {
        $this->Credentials = $credentials;
        $this->Environment = $environment;
    }
}
<?php

namespace BraspagSdk\Velocity;

use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Contracts\Velocity\MerchantCredentials;

class VelocityClientOptions extends ClientOptions
{
    public $Credentials;

    /**
     * VelocityClientOptions constructor.
     * @param $credentials
     * @param $environment
     */
    public function __construct(MerchantCredentials $credentials, $environment = null)
    {
        $this->Credentials = $credentials;
        $this->Environment = $environment;
    }
}
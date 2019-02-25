<?php

namespace BraspagSdk\Velocity;

use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;

class VelocityClient
{
    private $credentials;
    private $url;

    public function __construct(VelocityClientOptions $options)
    {
        $this->credentials = $options->credentials;

        if ($options->Environment == Environment::PRODUCTION)
        {
            $this->url = Endpoints::VelocityApiProduction;
        }
        else
        {
            $this->url = Endpoints::VelocityApiSandbox;
        }
    }
}
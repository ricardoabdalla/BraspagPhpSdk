<?php

namespace BraspagSdk\Common;

class ClientOptions
{
    public $Environment;

    /**
     * ClientOptions constructor.
     * @param $environment
     */
    public function __construct($environment = null)
    {
        $this->Environment = $environment;
    }
}
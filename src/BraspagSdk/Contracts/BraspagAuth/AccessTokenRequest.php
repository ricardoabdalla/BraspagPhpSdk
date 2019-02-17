<?php

namespace BraspagSdk\Contracts\BraspagAuth;

class AccessTokenRequest
{
    public $ClientId;

    public $ClientSecret;

    public $GrantType;

    public $Scope;

    public $RefreshToken;

    public $Username;

    public $Password;
}
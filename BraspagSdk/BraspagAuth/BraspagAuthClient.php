<?php

namespace BraspagSdk\BraspagAuth;

use BraspagSdk\Common\Environment;
use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;
use BraspagSdk\Common\ClientOptions;
use InvalidArgumentException;

class BraspagAuthClient
{
    private $url;

    public function __construct(ClientOptions $clientOptions)
    {
        if ($clientOptions->Environment == Environment::PRODUCTION)
            $this->url = Endpoints::BraspagAuthProduction;
        else
            $this->url = Endpoints::BraspagAuthSandbox;

        $this->url .= "oauth2/token";
    }

    function CreateAccessToken(AccessTokenRequest $request)
    {
        if ($request == null)
            throw new InvalidArgumentException("Request is null");

        if (empty($request->ClientId) || !isset($request->ClientId))
            throw new InvalidArgumentException("Invalid credentials: ClientId is null or empty");

        if (empty($request->ClientSecret) || !isset($request->ClientSecret))
            throw new InvalidArgumentException("Invalid credentials: ClientSecret is null or empty");

        $params = "grant_type=$request->GrantType";

        switch ($request->GrantType)
        {
            case OAuthGrantType::Password :
                $params .= "&username=$request->Username&password=$request->Password";
                break;
            case OAuthGrantType::RefreshToken :
                $params .= "&refresh_token=$request->RefreshToken";
                break;
        }

        if (empty($request->RefreshToken) || !isset($request->RefreshToken))
            $params .= "&scope=$request->Scope";


        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $request->ClientId . ":" . $request->ClientSecret);
        curl_setopt($curl, CURLOPT_URL, $this->url);

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Braspag PHP SDK',
            "cache-control: no-cache"
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }
}
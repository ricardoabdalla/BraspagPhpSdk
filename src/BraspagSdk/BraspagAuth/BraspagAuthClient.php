<?php

namespace BraspagSdk\BraspagAuth;

use BraspagSdk\Common\Environment;
use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\OAuthGrantType;
use BraspagSdk\Common\ClientOptions;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenRequest;
use BraspagSdk\Contracts\BraspagAuth\AccessTokenResponse;
use InvalidArgumentException;
use Exception;

class BraspagAuthClient
{
    private $url;

    public function __construct(ClientOptions $clientOptions)
    {
        if ($clientOptions->Environment == Environment::PRODUCTION)
            $this->url = Endpoints::BraspagAuthProduction;
        else
            $this->url = Endpoints::BraspagAuthSandbox;
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

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $request->ClientId . ":" . $request->ClientSecret);
            curl_setopt($curl, CURLOPT_URL, $this->url . "oauth2/token");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', "cache-control: no-cache"));
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            $response = curl_exec($curl);

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($errno = curl_errno($curl)) {
                $error_message = curl_strerror($errno);
                $curl_error = curl_error($curl);
            }

            curl_close($curl);
        }
        catch (Exception $e)
        {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR
            );
        }

        if (!empty($response) || isset($response))
        {
            $jsonResponse = AccessTokenResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new AccessTokenResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->Error = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDescription = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }
}
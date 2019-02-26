<?php

namespace BraspagSdk\Velocity;

use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;
use BraspagSdk\Contracts\Velocity\AnalysisRequest;
use BraspagSdk\Contracts\Velocity\AnalysisResponse;
use BraspagSdk\Contracts\Velocity\ErrorData;
use BraspagSdk\Contracts\Velocity\MerchantCredentials;
use InvalidArgumentException;
use Exception;

class VelocityClient
{
    private $credentials;
    private $url;

    public function __construct(VelocityClientOptions $options)
    {
        $this->credentials = $options->Credentials;

        if ($options->Environment == Environment::PRODUCTION)
        {
            $this->url = Endpoints::VelocityApiProduction;
        }
        else
        {
            $this->url = Endpoints::VelocityApiSandbox;
        }
    }

    function performAnalysis(AnalysisRequest $request, MerchantCredentials $credentials = null)
    {
        if (empty($request))
            throw new InvalidArgumentException("Request is null");

        if (empty($this->credentials) && empty($credentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $credentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->AccessToken))
            throw new InvalidArgumentException("Invalid credentials: AccessToken is null");

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "analysis/v2/");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag-SDK-PHP');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "MerchantId: $currentCredentials->MerchantId",
                "Authorization: Bearer $currentCredentials->MerchantId",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $jsonRequest = json_encode($request);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonRequest);

            $httpResponse = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
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

        if (!empty($httpResponse))
        {
            $response = AnalysisResponse::fromJson($httpResponse);
            $response->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $response;
        }
        else
        {
            $errorResponse = new AnalysisResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorCollection = array();
            $errorData = new ErrorData();
            $errorData->ErrorCode = isset($curl_error) ? $curl_error : "unknown_error";
            $errorData->Message = isset($error_message) ? $error_message : "Unknown error";
            array_push($errorCollection, $errorData);
            $errorResponse->ErrorDataCollection = $errorCollection;
            return $errorResponse;
        }
    }
}
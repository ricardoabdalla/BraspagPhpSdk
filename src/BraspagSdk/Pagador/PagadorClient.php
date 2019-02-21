<?php

namespace BraspagSdk\Pagador;

use BraspagSdk\Contracts\Pagador\MerchantCredentials;
use BraspagSdk\Contracts\Pagador\SaleRequest;
use BraspagSdk\Contracts\Pagador\SaleResponse;
use BraspagSdk\Common\Endpoints;
use BraspagSdk\Common\Environment;
use InvalidArgumentException;
use Exception;

class PagadorClient
{
    private $credentials;
    private $url;

    public function __construct(PagadorClientOptions $pagadorClientOptions)
    {
        $this->credentials = $pagadorClientOptions->credentials;

        if ($pagadorClientOptions->Environment == Environment::PRODUCTION)
            $this->url = Endpoints::PagadorApiProduction;
        else
            $this->url = Endpoints::PagadorApiSandbox;
    }

    function createSale(SaleRequest $saleRequest, MerchantCredentials $merchantCredentials = null)
    {
        if (empty($saleRequest))
            throw new InvalidArgumentException("Sale request is null");

        if (empty($this->credentials) && empty($merchantCredentials))
            throw new InvalidArgumentException("Credentials are null");

        $currentCredentials = $this->credentials ?: $merchantCredentials;

        if (empty($currentCredentials->MerchantId))
            throw new InvalidArgumentException("Invalid credentials: MerchantId is null");

        if (empty($currentCredentials->MerchantKey))
            throw new InvalidArgumentException("Invalid credentials: MerchantKey is null");

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_URL, $this->url . "v2/sales");
            curl_setopt($curl, CURLOPT_USERAGENT, 'Braspag PHP SDK');
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "User-Agent: Braspag PHP SDK",
                "MerchantId: $currentCredentials->MerchantId",
                "MerchantKey: $currentCredentials->MerchantKey",
                "RequestId: " . uniqid(),
                "cache-control: no-cache"
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $saleRequestJson = json_encode($saleRequest);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $saleRequestJson);

            $response = curl_exec($curl);

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

        if (!empty($response))
        {
            $jsonResponse = SaleResponse::fromJson($response);
            $jsonResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            return $jsonResponse;
        }
        else
        {
            $errorResponse = new SaleResponse();
            $errorResponse->HttpStatus = isset($statusCode) ? $statusCode : 0;
            $errorResponse->ErrorDataCollection->Code = isset($curl_error) ? $curl_error : "unknown_error";
            $errorResponse->ErrorDataCollection->Message = isset($error_message) ? $error_message : "Unknown error";
            return $errorResponse;
        }
    }
}